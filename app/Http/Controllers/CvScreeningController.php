<?php

namespace App\Http\Controllers;

use App\Models\CvScreening;
use App\Models\CvScreeningCandidate;
use App\Services\CvAnalysisService;
use App\Support\KeywordMatcher;
use App\Support\VectorSimilarity;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Analisador de CVs para recrutadores.
 *
 * Ao contrário da análise de candidaturas (CompanyController), aqui não é preciso
 * publicar uma vaga: o recrutador escreve a descrição, carrega os CVs que tiver em
 * mão e recebe a lista ordenada do mais compatível para o menos compatível.
 */
class CvScreeningController extends Controller
{
    public function index()
    {
        $screenings = CvScreening::where('user_id', Auth::id())
            ->withCount('candidates')
            ->orderByDesc('id')
            ->get();

        return view('cv-screenings.index', compact('screenings'));
    }

    public function store(Request $request)
    {
        $files = $this->validFiles($request);
        $request->files->set('cvs', $files->all());

        $validated = $request->validate(
            [
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:20000',
                'cvs' => 'required|array|min:1|max:' . CvScreening::MAX_CVS_PER_UPLOAD,
                'cvs.*' => ['file', 'max:' . CvScreening::MAX_CV_SIZE_KB, $this->pdfRule()],
            ],
            $this->uploadMessages(CvScreening::MAX_CVS_PER_UPLOAD)
        );

        $screening = CvScreening::create([
            'user_id' => Auth::id(),
            'company_id' => Auth::user()->company?->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
        ]);

        $this->storeCvs($screening, $files);

        return redirect()
            ->route('cv-screenings.show', $screening)
            ->with('success', 'Triagem criada. Clique em "Analisar CVs" para ordenar os candidatos.');
    }

    public function show(CvScreening $screening)
    {
        $this->authorizeScreening($screening);

        $screeningHasCurrentVector = $screening->hasCurrentVector();
        $keywords = KeywordMatcher::extractKeywords($screening->description);

        $candidates = $screening->candidates()->orderBy('id')->get()
            ->map(function (CvScreeningCandidate $candidate) use ($screening, $screeningHasCurrentVector, $keywords) {
                $candidateHasCurrentVector = $candidate->hasCurrentVector();

                $semanticScore = ($screeningHasCurrentVector && $candidateHasCurrentVector)
                    ? VectorSimilarity::cosine($screening->description_vector, $candidate->cv_vector)
                    : null;

                $keywordResult = KeywordMatcher::score($keywords, $candidate->cv_text);

                $candidate->match_score = KeywordMatcher::blend($semanticScore, $keywordResult['score'] ?? null);
                $candidate->has_current_vector = $candidateHasCurrentVector;
                $candidate->matched_keywords = $keywordResult['matched'] ?? [];

                return $candidate;
            })
            ->sortByDesc(fn (CvScreeningCandidate $candidate) => $candidate->match_score ?? -2)
            ->values();

        return view('cv-screenings.show', compact('screening', 'candidates', 'screeningHasCurrentVector'));
    }

    public function addCvs(Request $request, CvScreening $screening)
    {
        $this->authorizeScreening($screening);

        $remaining = $screening->remainingSlots();

        if ($remaining < 1) {
            return back()->with('error', 'Esta triagem já tem o máximo de ' . CvScreening::MAX_CVS_PER_SCREENING . ' CVs.');
        }

        $maxNow = min($remaining, CvScreening::MAX_CVS_PER_UPLOAD);
        $files = $this->validFiles($request);
        $request->files->set('cvs', $files->all());

        $request->validate(
            [
                'cvs' => 'required|array|min:1|max:' . $maxNow,
                'cvs.*' => ['file', 'max:' . CvScreening::MAX_CV_SIZE_KB, $this->pdfRule()],
            ],
            $this->uploadMessages($maxNow)
        );

        $this->storeCvs($screening, $files);

        return back()->with('success', $files->count() . ' CV(s) adicionado(s). Clique em "Analisar CVs" para actualizar a ordenação.');
    }

    public function analyzeDescription(CvScreening $screening, CvAnalysisService $analysis)
    {
        $this->authorizeScreening($screening);

        $result = $analysis->embed(trim(strip_tags($screening->description)));

        if (!$result) {
            return response()->json([
                'ok' => false,
                'message' => 'Não foi possível contactar o serviço de análise de CVs. Tente novamente dentro de momentos.',
            ], 502);
        }

        $screening->update([
            'description_vector' => $result['vector'],
            'description_vector_model' => $result['model'],
            'description_vector_generated_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function analyzeCandidate(CvScreening $screening, CvScreeningCandidate $candidate, CvAnalysisService $analysis)
    {
        $this->authorizeScreening($screening);
        $this->authorizeCandidate($screening, $candidate);

        // O OCR de um CV digitalizado pode demorar; o limite de execução por omissão
        // do alojamento partilhado (30-60s) cortaria o pedido antes da resposta.
        set_time_limit(130);

        $result = $analysis->analyzeCv('local', $candidate->path);

        if (!$result) {
            return response()->json([
                'ok' => false,
                'message' => 'Não foi possível analisar este CV. Tente novamente dentro de momentos.',
            ], 502);
        }

        $candidate->update([
            'cv_text' => $result['text'],
            'cv_vector' => $result['vector'],
            'cv_vector_model' => $result['model'],
            'cv_analyzed_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function download(CvScreening $screening, CvScreeningCandidate $candidate)
    {
        $this->authorizeScreening($screening);
        $this->authorizeCandidate($screening, $candidate);

        if (!Storage::disk('local')->exists($candidate->path)) {
            abort(404);
        }

        return Storage::disk('local')->download($candidate->path, $candidate->original_name ?: basename($candidate->path));
    }

    public function destroyCandidate(CvScreening $screening, CvScreeningCandidate $candidate)
    {
        $this->authorizeScreening($screening);
        $this->authorizeCandidate($screening, $candidate);

        Storage::disk('local')->delete($candidate->path);
        $candidate->delete();

        return back()->with('success', 'CV removido da triagem.');
    }

    public function destroy(CvScreening $screening)
    {
        $this->authorizeScreening($screening);

        Storage::disk('local')->deleteDirectory($screening->storageDirectory());
        $screening->delete();

        return redirect()->route('cv-screenings.index')->with('success', 'Triagem removida.');
    }

    /**
     * Descarta entradas vazias do input de ficheiros (um `<input multiple>` sem
     * selecção chega como um ficheiro inválido de 0 bytes) antes da validação.
     *
     * @return \Illuminate\Support\Collection<int, UploadedFile>
     */
    private function validFiles(Request $request)
    {
        return collect($request->file('cvs', []))
            ->filter(fn ($file) => $file instanceof UploadedFile && $file->isValid() && $file->getSize() > 0)
            ->values();
    }

    /**
     * O serviço de análise só extrai texto de PDF, por isso rejeitamos aqui o que
     * nunca chegaria a ser analisado, em vez de deixar o recrutador descobrir
     * depois do carregamento.
     */
    private function pdfRule(): \Closure
    {
        return function ($attribute, $value, $fail) {
            if (!$value instanceof UploadedFile) {
                $fail('Cada CV deve ser um ficheiro.');

                return;
            }

            if (strtolower($value->getClientOriginalExtension()) !== 'pdf') {
                $fail('Cada CV deve estar em formato PDF.');
            }
        };
    }

    /**
     * @return array<string, string>
     */
    private function uploadMessages(int $max): array
    {
        return [
            'cvs.required' => 'Carregue pelo menos um CV em PDF.',
            'cvs.min' => 'Carregue pelo menos um CV em PDF.',
            'cvs.max' => 'Pode carregar no máximo ' . $max . ' CV(s) de cada vez.',
            'cvs.*.max' => 'Cada CV não pode ultrapassar 5 MB.',
        ];
    }

    /**
     * @param \Illuminate\Support\Collection<int, UploadedFile> $files
     */
    private function storeCvs(CvScreening $screening, $files): void
    {
        foreach ($files as $file) {
            $path = $file->store($screening->storageDirectory(), 'local');

            CvScreeningCandidate::create([
                'cv_screening_id' => $screening->id,
                'path' => $path,
                'original_name' => Str::limit($file->getClientOriginalName(), 255, ''),
            ]);
        }
    }

    private function authorizeScreening(CvScreening $screening): void
    {
        if ((int) $screening->user_id !== (int) Auth::id()) {
            abort(403);
        }
    }

    private function authorizeCandidate(CvScreening $screening, CvScreeningCandidate $candidate): void
    {
        if ((int) $candidate->cv_screening_id !== (int) $screening->id) {
            abort(404);
        }
    }
}

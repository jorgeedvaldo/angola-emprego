<?php

namespace App\Http\Controllers;

use App\Services\CvAnalysisService;
use App\Support\KeywordMatcher;
use App\Support\VectorSimilarity;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Analisador de CVs público — sem conta e sem gravar nada.
 *
 * Todo o estado (descrição escrita, ficheiros escolhidos, pontuações e a ordem
 * final) vive no browser do recrutador. O servidor só entra em dois momentos, e
 * em ambos apenas reencaminha para o serviço de análise e devolve o resultado:
 *
 *  1. /vaga — transforma a descrição em vector e extrai as palavras-chave;
 *  2. /cv   — recebe um PDF, obtém o texto/vector e devolve a pontuação.
 *
 * O PDF nunca é gravado: vive no directório temporário do PHP durante o pedido e
 * é descartado no fim. A análise de candidaturas das empresas (CompanyController)
 * é outra coisa e continua a gravar as pontuações na base de dados.
 */
class CvAnalyzerController extends Controller
{
    /** Tamanho máximo de cada CV, em KB (regra de validação do Laravel). */
    public const MAX_CV_SIZE_KB = 5120;

    /** Máximo de CVs por análise — limite aplicado no browser. */
    public const MAX_CVS = 30;

    /** Tecto defensivo para a lista de palavras-chave devolvida pelo browser. */
    private const MAX_KEYWORDS = 300;

    public function index()
    {
        return view('cv-analyzer.index');
    }

    /**
     * Passo 1: descrição da vaga -> vector + palavras-chave.
     *
     * As palavras-chave são extraídas aqui (e não no browser) para que o
     * KeywordMatcher continue a ser a única fonte da verdade da pontuação.
     */
    public function analyzeJob(Request $request, CvAnalysisService $analysis)
    {
        $validated = $request->validate([
            'description' => 'required|string|min:30|max:20000',
        ], [
            'description.required' => 'Escreva a descrição da vaga.',
            'description.min' => 'A descrição está demasiado curta para comparar com os CVs.',
        ]);

        $result = $analysis->embed(trim(strip_tags($validated['description'])));

        if (!$result) {
            return response()->json([
                'ok' => false,
                'message' => 'Não foi possível contactar o serviço de análise de CVs. Tente novamente dentro de momentos.',
            ], 502);
        }

        return response()->json([
            'ok' => true,
            'vector' => $result['vector'],
            'model' => $result['model'],
            'keywords' => KeywordMatcher::extractKeywords($validated['description']),
        ]);
    }

    /**
     * Passo 2: um CV -> pontuação de compatibilidade com a vaga do passo 1.
     *
     * O vector e as palavras-chave da vaga voltam do browser em cada pedido, o
     * que evita guardar sessão ou repetir o embedding da descrição por CV.
     */
    public function analyzeCv(Request $request, CvAnalysisService $analysis)
    {
        // O OCR de um CV digitalizado pode demorar; o limite de execução por
        // omissão do alojamento partilhado (30-60s) cortaria o pedido antes da
        // resposta do serviço.
        set_time_limit(130);

        $request->validate([
            'cv' => ['required', 'file', 'max:' . self::MAX_CV_SIZE_KB, $this->pdfRule()],
            'vector' => 'required|string|max:200000',
            'model' => 'required|string|max:120',
            'keywords' => 'nullable|string|max:200000',
        ], [
            'cv.required' => 'Escolha um CV em PDF.',
            'cv.max' => 'Cada CV não pode ultrapassar 5 MB.',
        ]);

        $jobVector = json_decode($request->input('vector'), true);

        if (!VectorSimilarity::isValidVector($jobVector)) {
            return response()->json([
                'ok' => false,
                'message' => 'A análise da vaga expirou. Actualize a página e comece de novo.',
            ], 422);
        }

        /** @var UploadedFile $file */
        $file = $request->file('cv');
        $contents = (string) file_get_contents($file->getRealPath());

        // Um ficheiro vazio faria o serviço de análise rejeitar o pedido com um
        // erro genérico; mais vale dizer ao recrutador o que se passou.
        if ($contents === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Este ficheiro está vazio ou não pôde ser lido.',
            ], 422);
        }

        $result = $analysis->analyzeCvContents($contents, $file->getClientOriginalName());

        if (!$result) {
            return response()->json([
                'ok' => false,
                'message' => 'Não foi possível analisar este CV. Tente novamente dentro de momentos.',
            ], 502);
        }

        // Vectores de modelos diferentes não são comparáveis: se o serviço tiver
        // sido actualizado entre o passo 1 e o passo 2, fica só a pontuação por
        // palavras-chave em vez de uma semelhança sem significado.
        $semanticScore = $result['model'] === $request->input('model')
            ? VectorSimilarity::cosine($jobVector, $result['vector'])
            : null;

        $keywordResult = KeywordMatcher::score($this->keywords($request->input('keywords')), $result['text']);

        return response()->json([
            'ok' => true,
            'score' => KeywordMatcher::blend($semanticScore, $keywordResult['score'] ?? null),
            'matched' => array_slice($keywordResult['matched'] ?? [], 0, 12),
        ]);
    }

    /**
     * O serviço de análise só extrai texto de PDF, por isso rejeitamos aqui o que
     * nunca chegaria a ser analisado.
     */
    private function pdfRule(): \Closure
    {
        return function ($attribute, $value, $fail) {
            if (!$value instanceof UploadedFile) {
                $fail('O CV deve ser um ficheiro.');

                return;
            }

            if (strtolower($value->getClientOriginalExtension()) !== 'pdf') {
                $fail('O CV deve estar em formato PDF.');
            }
        };
    }

    /**
     * Normaliza as palavras-chave devolvidas pelo browser: são dados vindos do
     * cliente, por isso a forma de cada entrada é verificada antes de chegar ao
     * KeywordMatcher.
     *
     * @return array<int, array{term: string, weight: float}>
     */
    private function keywords(?string $json): array
    {
        $decoded = json_decode((string) $json, true);

        if (!is_array($decoded)) {
            return [];
        }

        $keywords = [];

        foreach (array_slice($decoded, 0, self::MAX_KEYWORDS) as $entry) {
            if (!is_array($entry) || !isset($entry['term'], $entry['weight'])) {
                continue;
            }

            if (!is_string($entry['term']) || !is_numeric($entry['weight'])) {
                continue;
            }

            $term = trim($entry['term']);

            if ($term === '') {
                continue;
            }

            $keywords[] = [
                'term' => Str::limit($term, 100, ''),
                'weight' => (float) $entry['weight'],
            ];
        }

        return $keywords;
    }
}

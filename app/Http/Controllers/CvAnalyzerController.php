<?php

namespace App\Http\Controllers;

use App\Services\CvAnalysisService;
use App\Support\JobRequirements;
use App\Support\KeywordMatcher;
use App\Support\RequirementMatcher;
use App\Support\TextChunker;
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
 *  1. /vaga — separa a vaga nos requisitos que pede e transforma cada um em vector;
 *  2. /cv   — recebe um PDF, parte-o em blocos e diz que requisitos cada bloco cumpre.
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

    /**
     * Casas decimais com que os vectores viajam para o browser. A semelhança de
     * coseno não nota a diferença e o pedido de cada CV fica com metade do tamanho.
     */
    private const VECTOR_PRECISION = 5;

    public function index()
    {
        return view('cv-analyzer.index');
    }

    /**
     * Passo 1: a vaga -> requisitos individuais em vector + palavras-chave.
     *
     * As palavras-chave são extraídas aqui (e não no browser) para que o
     * KeywordMatcher continue a ser a única fonte da verdade da pontuação.
     */
    public function analyzeJob(Request $request, CvAnalysisService $analysis)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'required|string|min:30|max:20000',
        ], [
            'description.required' => 'Escreva a descrição da vaga.',
            'description.min' => 'A descrição está demasiado curta para comparar com os CVs.',
        ]);

        $description = $validated['description'];
        // O título é a linha mais específica de um anúncio: há empregadores que
        // publicam vários cargos com o mesmo bloco de requisitos, e sem o título
        // esses cargos ficam indistinguíveis.
        $title = $validated['title'] ?? null;
        $lines = JobRequirements::lines($description);

        // Um requisito solto não dá uma análise por requisitos que se aproveite;
        // nesse caso fica a comparação com o texto todo, como fallback.
        $textsToEmbed = count($lines) >= 2
            ? $lines
            : [JobRequirements::requirementsText($description, $title)];

        $embedded = $analysis->embedMany($textsToEmbed);

        if (!$embedded) {
            return response()->json([
                'ok' => false,
                'message' => 'Não foi possível contactar o serviço de análise de CVs. Tente novamente dentro de momentos.',
            ], 502);
        }

        $requirements = [];

        if (count($lines) >= 2) {
            foreach ($lines as $index => $line) {
                $requirements[] = [
                    'text' => $line,
                    'vector' => $this->round($embedded['vectors'][$index]),
                ];
            }
        }

        return response()->json([
            'ok' => true,
            'model' => $embedded['model'],
            // Usado quando não há requisitos identificáveis: comparação do CV
            // inteiro com a vaga inteira, o comportamento antigo.
            'vector' => $this->round($embedded['vectors'][0]),
            'requirements' => $requirements,
            'keywords' => KeywordMatcher::extractKeywords($description, $title),
        ]);
    }

    /**
     * Passo 2: um CV -> que requisitos cumpre e pontuação final.
     *
     * Os requisitos (texto e vector) voltam do browser em cada pedido, o que evita
     * guardar sessão ou repetir a análise da vaga a cada CV.
     */
    public function analyzeCv(Request $request, CvAnalysisService $analysis)
    {
        // O OCR de um CV digitalizado pode demorar, e a este passo somam-se ainda
        // os vectores de cada bloco; o limite por omissão do alojamento partilhado
        // (30-60s) cortaria o pedido a meio.
        set_time_limit(180);

        $request->validate([
            'cv' => ['required', 'file', 'max:' . self::MAX_CV_SIZE_KB, $this->pdfRule()],
            'vector' => 'required|string|max:200000',
            'model' => 'required|string|max:120',
            'keywords' => 'nullable|string|max:200000',
            'requirements' => 'nullable|string|max:2000000',
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
        $sameModel = $result['model'] === $request->input('model');
        $requirements = $this->requirements($request->input('requirements'));
        $keywordResult = KeywordMatcher::score($this->keywords($request->input('keywords')), $result['text']);

        if ($requirements !== [] && $sameModel) {
            return $this->requirementResponse($analysis, $requirements, $result, $keywordResult);
        }

        // Sem requisitos identificáveis (ou com o modelo trocado a meio), volta-se
        // à comparação do CV inteiro com a vaga inteira.
        $semanticScore = $sameModel ? VectorSimilarity::cosine($jobVector, $result['vector']) : null;

        return response()->json([
            'ok' => true,
            'score' => KeywordMatcher::blend($semanticScore, $keywordResult['score'] ?? null),
            'matched' => array_slice($keywordResult['matched'] ?? [], 0, 12),
            'requirements' => [],
        ]);
    }

    /**
     * @param array<int, array{text: string, vector: array<int, float>}> $requirements
     * @param array{text: string|null, vector: array<int, float>, model: string} $cv
     * @param array{score: float, matched: array<int, string>, total: int}|null $keywordResult
     */
    private function requirementResponse(
        CvAnalysisService $analysis,
        array $requirements,
        array $cv,
        ?array $keywordResult
    ) {
        $chunks = TextChunker::chunk((string) $cv['text']);
        $embedded = $chunks === [] ? null : $analysis->embedMany($chunks);

        // Se os blocos falharem, o vector do CV inteiro ainda serve de bloco único:
        // pior resolução, mas melhor do que devolver um erro ao recrutador.
        $chunkVectors = $embedded['vectors'] ?? [$cv['vector']];

        $evaluation = RequirementMatcher::evaluate($requirements, $chunkVectors, $cv['text']);

        return response()->json([
            'ok' => true,
            'score' => $evaluation['score'],
            'matched' => array_slice($keywordResult['matched'] ?? [], 0, 12),
            'requirements' => $evaluation['requirements'],
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

    /**
     * Idem para os requisitos: texto e vector de cada um chegam do browser e só
     * entram na análise se tiverem a forma esperada.
     *
     * @return array<int, array{text: string, vector: array<int, float>}>
     */
    private function requirements(?string $json): array
    {
        $decoded = json_decode((string) $json, true);

        if (!is_array($decoded)) {
            return [];
        }

        $requirements = [];

        foreach (array_slice($decoded, 0, JobRequirements::MAX_LINES) as $entry) {
            if (!is_array($entry) || !isset($entry['text'], $entry['vector'])) {
                continue;
            }

            if (!is_string($entry['text']) || !VectorSimilarity::isValidVector($entry['vector'])) {
                continue;
            }

            $text = trim($entry['text']);

            if ($text === '') {
                continue;
            }

            $requirements[] = [
                'text' => Str::limit($text, 240, ''),
                'vector' => array_map('floatval', $entry['vector']),
            ];
        }

        return $requirements;
    }

    /**
     * @param array<int, float> $vector
     * @return array<int, float>
     */
    private function round(array $vector): array
    {
        return array_map(fn ($value) => round((float) $value, self::VECTOR_PRECISION), $vector);
    }
}

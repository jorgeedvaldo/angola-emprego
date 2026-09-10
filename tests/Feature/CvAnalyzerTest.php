<?php

namespace Tests\Feature;

use App\Support\VectorSimilarity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CvAnalyzerTest extends TestCase
{
    use RefreshDatabase;

    private const VAGA = <<<TXT
    Vaga para Técnico(a) de Recursos Humanos
    O Grupo Terra está a recrutar um(a) Técnico(a) de Recursos Humanos para integrar a sua equipa.

    Requisitos
    Experiência mínima de 2 anos na função
    Sólidos conhecimentos da Lei Geral do Trabalho de Angola
    Domínio avançado de Excel
    Diferencial: Conhecimento do ERP Primavera.

    CANDIDATURAS
    Envie o seu currículo para: rh@exemplo.co.ao
    TXT;

    private const CV_RECURSOS_HUMANOS = <<<TXT
    Técnica de Recursos Humanos com 4 anos de experiência na função.
    Processamento salarial e administração de pessoal numa empresa de 200 colaboradores.
    Sólidos conhecimentos da Lei Geral do Trabalho de Angola.
    Domínio avançado de Excel, com tabelas dinâmicas.
    TXT;

    private const CV_ANALISES_CLINICAS = <<<TXT
    Técnica de Análises Clínicas com Ensino Médio e 3 anos de experiência na clínica.
    Atendimento ao público e receção de pacientes no laboratório.
    Colheita de amostras e orientação ao paciente sobre o preparo.
    Noções básicas de enfermagem e biossegurança.
    TXT;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.analisecv.url' => 'https://analisecv.test',
            'services.analisecv.api_key' => 'chave',
        ]);
    }

    public function test_recruiters_hub_lists_the_three_options()
    {
        $this->get(route('recruiters.index'))
            ->assertOk()
            ->assertSee('Criar empresa')
            ->assertSee('Lista de empresas')
            ->assertSee('Analisar CV');
    }

    public function test_analyzer_is_open_without_an_account()
    {
        $this->get(route('cv-analyzer.index'))
            ->assertOk()
            ->assertSee('Analisador de CVs')
            ->assertSee('Os CVs não são guardados.');
    }

    public function test_job_description_returns_the_vector_and_the_keywords()
    {
        Http::fake(['*/embed' => Http::response([
            'ok' => true,
            'vector' => $this->vector(1.0),
            'model' => VectorSimilarity::MODEL_ID,
        ])]);

        $response = $this->postJson(route('cv-analyzer.job'), [
            'description' => 'Procuramos soldador com certificação em soldadura industrial e experiência offshore.',
        ]);

        $response->assertOk()->assertJson(['ok' => true, 'model' => VectorSimilarity::MODEL_ID]);
        $this->assertCount(VectorSimilarity::DIMENSIONS, $response->json('vector'));
        $this->assertNotEmpty($response->json('keywords'));
    }

    public function test_a_short_description_is_rejected()
    {
        $this->postJson(route('cv-analyzer.job'), ['description' => 'Soldador'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('description');
    }

    public function test_cv_is_scored_without_being_stored_anywhere()
    {
        Storage::fake('local');
        Http::fake(['*/analyze-cv' => Http::response([
            'ok' => true,
            'text' => 'Soldadura industrial com certificação e experiência offshore.',
            'vector' => $this->vector(1.0),
            'model' => VectorSimilarity::MODEL_ID,
        ])]);

        $response = $this->post(route('cv-analyzer.cv'), [
            'cv' => $this->pdf('candidato.pdf'),
            'vector' => json_encode($this->vector(1.0)),
            'model' => VectorSimilarity::MODEL_ID,
            'keywords' => json_encode([
                ['term' => 'soldadura industrial', 'weight' => 3.0],
                ['term' => 'offshore', 'weight' => 2.0],
            ]),
        ]);

        $response->assertOk()->assertJson(['ok' => true]);
        $this->assertGreaterThan(0.9, $response->json('score'));
        $this->assertContains('soldadura industrial', $response->json('matched'));

        // Nada gravado: nem ficheiros, nem tabelas para os guardar.
        $this->assertSame([], Storage::disk('local')->allFiles());
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasTable('cv_screenings'));
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasTable('cv_screening_candidates'));
    }

    public function test_a_cv_that_does_not_match_the_job_scores_low()
    {
        Http::fake(['*/analyze-cv' => Http::response([
            'ok' => true,
            'text' => 'Motorista de pesados com carta de condução.',
            'vector' => $this->vector(-1.0),
            'model' => VectorSimilarity::MODEL_ID,
        ])]);

        $response = $this->post(route('cv-analyzer.cv'), [
            'cv' => $this->pdf('motorista.pdf'),
            'vector' => json_encode($this->vector(1.0)),
            'model' => VectorSimilarity::MODEL_ID,
            'keywords' => json_encode([['term' => 'soldadura industrial', 'weight' => 3.0]]),
        ]);

        $response->assertOk();
        $this->assertLessThan(0, $response->json('score'));
    }

    public function test_only_pdf_cvs_are_accepted()
    {
        $this->postJson(route('cv-analyzer.cv'), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('cv');

        $this->post(route('cv-analyzer.cv'), [
            'cv' => UploadedFile::fake()->create('cv.docx', 80),
            'vector' => json_encode($this->vector(1.0)),
            'model' => VectorSimilarity::MODEL_ID,
        ])->assertSessionHasErrors('cv');
    }

    public function test_a_malformed_job_vector_is_refused()
    {
        Http::fake();

        $this->post(route('cv-analyzer.cv'), [
            'cv' => $this->pdf('candidato.pdf'),
            'vector' => json_encode([1, 2, 3]),
            'model' => VectorSimilarity::MODEL_ID,
        ])->assertStatus(422);

        Http::assertNothingSent();
    }

    public function test_service_failure_is_reported_without_breaking_the_page()
    {
        Http::fake(['*/analyze-cv' => Http::response(['ok' => false], 500)]);

        $this->post(route('cv-analyzer.cv'), [
            'cv' => $this->pdf('candidato.pdf'),
            'vector' => json_encode($this->vector(1.0)),
            'model' => VectorSimilarity::MODEL_ID,
        ])->assertStatus(502)->assertJson(['ok' => false]);
    }

    public function test_an_empty_file_is_reported_clearly()
    {
        Http::fake();

        $this->post(route('cv-analyzer.cv'), [
            'cv' => UploadedFile::fake()->createWithContent('vazio.pdf', ''),
            'vector' => json_encode($this->vector(1.0)),
            'model' => VectorSimilarity::MODEL_ID,
        ])->assertStatus(422)->assertJson(['ok' => false]);

        Http::assertNothingSent();
    }

    private function pdf(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, '%PDF-1.4 conteudo de teste');
    }

    public function test_the_job_is_broken_into_the_requirements_it_asks_for()
    {
        Http::fake(['*/embed' => Http::response([
            'ok' => true,
            'vector' => $this->vector(1.0),
            'model' => VectorSimilarity::MODEL_ID,
        ])]);

        $response = $this->postJson(route('cv-analyzer.job'), ['description' => self::VAGA]);

        $response->assertOk();
        $this->assertSame(
            [
                'Experiência mínima de 2 anos na função',
                'Sólidos conhecimentos da Lei Geral do Trabalho de Angola',
                'Domínio avançado de Excel',
                'Diferencial: Conhecimento do ERP Primavera.',
            ],
            array_column($response->json('requirements'), 'text')
        );
    }

    /**
     * O caso que motivou esta análise: um CV de Técnica de Análises Clínicas, sem
     * nada a ver com a vaga de Recursos Humanos, ficava lado a lado com os
     * candidatos da área. Aqui os embeddings são falsos mas coerentes — textos com
     * as mesmas palavras ficam próximos — para que a ordenação seja verificável.
     */
    public function test_an_unrelated_cv_ranks_far_below_a_matching_one()
    {
        $this->fakeAnalysisService();

        $vaga = $this->postJson(route('cv-analyzer.job'), ['description' => self::VAGA])->json();

        $rh = $this->scoreCv($vaga, self::CV_RECURSOS_HUMANOS);
        $clinica = $this->scoreCv($vaga, self::CV_ANALISES_CLINICAS);

        // Os limiares aqui são os que este embedding de teste consegue exprimir: por
        // ser um saco-de-palavras binário, penaliza requisitos curtos contra blocos
        // longos, coisa que um modelo de frases a sério não faz. O que o teste tem
        // de garantir é a distância entre os dois, que antes desta análise por
        // requisitos era de um único ponto percentual (74% contra 73%).
        $this->assertGreaterThan(0.5, $rh['score'], 'O CV da área devia pontuar alto.');
        $this->assertLessThan(0.25, $clinica['score'], 'O CV sem relação devia pontuar baixo.');
        $this->assertGreaterThan(
            $clinica['score'] + 0.35,
            $rh['score'],
            'A distância entre os dois tem de ser evidente, não de meia dúzia de pontos.'
        );
    }

    public function test_the_result_says_which_requirements_the_cv_meets()
    {
        $this->fakeAnalysisService();

        $vaga = $this->postJson(route('cv-analyzer.job'), ['description' => self::VAGA])->json();
        $rh = $this->scoreCv($vaga, self::CV_RECURSOS_HUMANOS);

        $porTexto = collect($rh['requirements'])->keyBy('text');

        $this->assertSame('cumpre', $porTexto['Domínio avançado de Excel']['status']);
        // O CV de RH não fala em Primavera: o ERP é o único requisito em falta.
        $this->assertSame('ausente', $porTexto['Diferencial: Conhecimento do ERP Primavera.']['status']);
    }

    public function test_an_advert_without_requirements_falls_back_to_whole_text_comparison()
    {
        $this->fakeAnalysisService();

        $vaga = $this->postJson(route('cv-analyzer.job'), [
            'description' => 'Precisamos de um técnico de recursos humanos para a nossa equipa em Luanda.',
        ])->json();

        $this->assertSame([], $vaga['requirements']);

        $resultado = $this->scoreCv($vaga, self::CV_RECURSOS_HUMANOS);

        $this->assertNotNull($resultado['score']);
        $this->assertSame([], $resultado['requirements']);
    }

    /**
     * Serviço de análise falso, mas coerente: o vector de um texto é a lista de
     * palavras do vocabulário que ele contém, por isso dois textos que falam do
     * mesmo ficam próximos e dois que falam de coisas diferentes ficam longe.
     */
    private function fakeAnalysisService(): void
    {
        Http::fake(function ($request) {
            if (str_contains($request->url(), '/embed')) {
                return Http::response([
                    'ok' => true,
                    'vector' => $this->topicVector($request->data()['text'] ?? ''),
                    'model' => VectorSimilarity::MODEL_ID,
                ]);
            }

            $texto = str_contains($request->body(), 'MARCA-RH')
                ? self::CV_RECURSOS_HUMANOS
                : self::CV_ANALISES_CLINICAS;

            return Http::response([
                'ok' => true,
                'text' => $texto,
                'vector' => $this->topicVector($texto),
                'model' => VectorSimilarity::MODEL_ID,
            ]);
        });
    }

    /**
     * @param array<string, mixed> $vaga
     * @return array<string, mixed>
     */
    private function scoreCv(array $vaga, string $cvText): array
    {
        $marca = $cvText === self::CV_RECURSOS_HUMANOS ? 'MARCA-RH' : 'MARCA-CLINICA';

        return $this->post(route('cv-analyzer.cv'), [
            'cv' => UploadedFile::fake()->createWithContent('cv.pdf', $marca . ' ' . $cvText),
            'vector' => json_encode($vaga['vector']),
            'model' => $vaga['model'],
            'keywords' => json_encode($vaga['keywords']),
            'requirements' => json_encode($vaga['requirements']),
        ])->assertOk()->json();
    }

    /**
     * @return array<int, float>
     */
    private function topicVector(string $text): array
    {
        $vocabulario = [
            'excel', 'primavera', 'salarial', 'pessoal', 'recursos', 'humanos',
            'trabalho', 'anos', 'clinica', 'paciente', 'laboratorio', 'enfermagem',
            'amostras', 'atendimento', 'lei',
        ];

        $normalizado = mb_strtolower($text, 'UTF-8');
        $vector = array_fill(0, VectorSimilarity::DIMENSIONS, 0.0);

        foreach ($vocabulario as $indice => $palavra) {
            $vector[$indice] = str_contains($normalizado, $palavra) ? 1.0 : 0.0;
        }

        // Evita o vector nulo, que não tem coseno definido.
        $vector[VectorSimilarity::DIMENSIONS - 1] = 0.01;

        return $vector;
    }

    /**
     * Vector alinhado (1.0) ou oposto (-1.0) ao da vaga, para tornar a semelhança
     * de coseno determinística no teste.
     */
    private function vector(float $direction): array
    {
        return array_fill(0, VectorSimilarity::DIMENSIONS, $direction);
    }
}

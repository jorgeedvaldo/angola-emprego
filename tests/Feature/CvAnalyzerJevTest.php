<?php

namespace Tests\Feature;

use App\Services\Cv\CvEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * O analisador público passa a poder pontuar de duas maneiras: pelos vectores,
 * como sempre, ou perguntando ao JEV.
 *
 * O que estes testes guardam, mais do que o JEV em si, é o interruptor: com a
 * variável apagada — ou com o JEV mal configurado — o analisador tem de
 * continuar a correr pelo caminho antigo, que é a rede de segurança de toda
 * esta alteração.
 */
class CvAnalyzerJevTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.analisecv.url' => 'https://analisecv.test',
            'services.analisecv.api_key' => 'chave-de-teste',
        ]);
    }

    private function ligarJev(): void
    {
        config([
            'services.cv_analyzer_engine' => 'jev',
            'services.jev.api_key' => 'chave-jev',
            'services.jev.url' => 'https://api.jev.test',
            'services.jev.endpoint' => '/v1/evaluate',
            'services.jev.model' => 'jev-latest',
        ]);
    }

    private function pdf(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('cv.pdf', '%PDF-1.4 conteúdo');
    }

    public function test_the_old_engine_runs_when_the_variable_is_not_set()
    {
        config(['services.cv_analyzer_engine' => null]);

        $this->assertSame(CvEngine::VECTORES, CvEngine::activo());
        $this->assertFalse(CvEngine::jevActivo());
    }

    /**
     * Uma configuração a meio — pediram o JEV mas a chave não está lá — não pode
     * deixar o analisador em baixo. Cai no motor antigo, que funciona.
     */
    public function test_asking_for_jev_without_a_key_falls_back_to_the_old_engine()
    {
        config(['services.cv_analyzer_engine' => 'jev', 'services.jev.api_key' => null]);

        $this->assertSame(CvEngine::VECTORES, CvEngine::activo());
        $this->assertFalse(CvEngine::jevActivo());
    }

    public function test_with_jev_on_the_job_step_does_not_call_the_embedding_service()
    {
        $this->ligarJev();
        Http::fake();

        $resposta = $this->postJson(route('cv-analyzer.job'), [
            'title' => 'Técnico de Recursos Humanos',
            'description' => 'Procuramos um técnico de recursos humanos com experiência em recrutamento.',
        ]);

        $resposta->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('motor', 'jev');

        // Com o JEV não há vector da vaga para calcular.
        Http::assertNothingSent();
    }

    public function test_with_jev_the_score_is_the_probability_it_returns()
    {
        $this->ligarJev();

        Http::fake([
            'analisecv.test/*' => Http::response(['ok' => true, 'text' => 'Currículo do candidato', 'vector' => [0.1], 'model' => 'LaBSE']),
            'api.jev.test/*' => Http::response(['answers' => ['compatibilidade' => ['probability' => 0.87]]]),
        ]);

        $resposta = $this->post(route('cv-analyzer.cv'), [
            'cv' => $this->pdf(),
            'description' => 'Procuramos um técnico de recursos humanos com experiência em recrutamento.',
        ]);

        $resposta->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('motor', 'jev')
            ->assertJsonPath('score', 0.87);
    }

    /**
     * A metodologia pedida: a descrição da vaga e o texto do CV vão os dois no
     * mesmo pedido ao JEV.
     */
    public function test_the_job_description_and_the_cv_text_both_reach_jev()
    {
        $this->ligarJev();

        Http::fake([
            'analisecv.test/*' => Http::response(['ok' => true, 'text' => 'TEXTO-OCR-DO-CV', 'vector' => [0.1], 'model' => 'LaBSE']),
            'api.jev.test/*' => Http::response(['answers' => ['compatibilidade' => ['probability' => 0.5]]]),
        ]);

        $this->post(route('cv-analyzer.cv'), [
            'cv' => $this->pdf(),
            'description' => 'DESCRICAO-DA-VAGA com requisitos suficientes para passar na validação.',
        ])->assertOk();

        Http::assertSent(function ($request) {
            if (!str_contains($request->url(), 'api.jev.test')) {
                return false;
            }

            $corpo = $request->data();

            return str_contains($corpo['state']['vaga'] ?? '', 'DESCRICAO-DA-VAGA')
                && str_contains($corpo['state']['curriculo'] ?? '', 'TEXTO-OCR-DO-CV')
                && ($corpo['questions'][0]['type'] ?? null) === 'noul';
        });
    }

    public function test_a_jev_failure_does_not_pass_as_a_zero_score()
    {
        $this->ligarJev();

        Http::fake([
            'analisecv.test/*' => Http::response(['ok' => true, 'text' => 'Currículo', 'vector' => [0.1], 'model' => 'LaBSE']),
            'api.jev.test/*' => Http::response(['erro' => 'qualquer coisa'], 500),
        ]);

        // Uma vaga sem resposta tem de aparecer como erro ao recrutador. Um zero
        // seria pior: passaria por um candidato mau, quando na verdade não se sabe.
        $this->post(route('cv-analyzer.cv'), [
            'cv' => $this->pdf(),
            'description' => 'Procuramos um técnico de recursos humanos com experiência em recrutamento.',
        ])->assertStatus(502)->assertJsonPath('ok', false);
    }

    /**
     * A resposta do JEV vem numa forma que não foi possível confirmar na
     * documentação, por isso o cliente procura a probabilidade em vários
     * sítios. Estes são os que ele conhece.
     *
     * @dataProvider formasDaResposta
     */
    public function test_it_reads_the_probability_from_the_shapes_it_knows(array $corpo, float $esperado)
    {
        $this->ligarJev();

        Http::fake([
            'analisecv.test/*' => Http::response(['ok' => true, 'text' => 'Currículo', 'vector' => [0.1], 'model' => 'LaBSE']),
            'api.jev.test/*' => Http::response($corpo),
        ]);

        $resposta = $this->post(route('cv-analyzer.cv'), [
            'cv' => $this->pdf(),
            'description' => 'Procuramos um técnico de recursos humanos com experiência em recrutamento.',
        ])->assertOk();

        // Comparado como float de propósito: um 1.0 viaja no JSON como 1, e a
        // comparação estrita do assertJsonPath tropeçaria nisso.
        $this->assertSame($esperado, (float) $resposta->json('score'));
    }

    public static function formasDaResposta(): array
    {
        return [
            'answers por id' => [['answers' => ['compatibilidade' => ['probability' => 0.42]]], 0.42],
            'answers por índice' => [['answers' => [['probability' => 0.33]]], 0.33],
            'results' => [['results' => [['value' => 0.71]]], 0.71],
            'probabilidade à cabeça' => [['probability' => 0.9], 0.9],
            'numa escala de 0 a 100' => [['probability' => 87], 0.87],
            'acima do limite' => [['probability' => 1.4], 1.0],
        ];
    }

    public function test_an_unreadable_jev_response_is_an_error_not_a_score()
    {
        $this->ligarJev();

        Http::fake([
            'analisecv.test/*' => Http::response(['ok' => true, 'text' => 'Currículo', 'vector' => [0.1], 'model' => 'LaBSE']),
            'api.jev.test/*' => Http::response(['algo' => 'que não reconhecemos']),
        ]);

        $this->post(route('cv-analyzer.cv'), [
            'cv' => $this->pdf(),
            'description' => 'Procuramos um técnico de recursos humanos com experiência em recrutamento.',
        ])->assertStatus(502);
    }
}

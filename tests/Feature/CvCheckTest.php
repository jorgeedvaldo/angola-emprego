<?php

namespace Tests\Feature;

use App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * "Verifique o seu CV": a outra ponta do analisador.
 *
 * No analisador é o recrutador que traz muitos CVs e a vaga que escreveu. Aqui
 * é o candidato que traz um CV, e a vaga é a da página — vem da base de dados,
 * e é isso que estes testes guardam com mais cuidado: quem verifica não pode
 * escolher contra o que é medido.
 */
class CvCheckTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.analisecv.url' => 'https://analisecv.test',
            'services.analisecv.api_key' => 'k',
            'services.cv_analyzer_engine' => 'jev',
            'services.jev.api_key' => 'sk',
            'services.jev.url' => 'https://api.jev.test',
            'services.jev.endpoint' => '/v1/systemone',
        ]);
    }

    private function pdf(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('cv.pdf', '%PDF-1.4 conteúdo');
    }

    private function fingir(float $noul = 0.8, string $texto = 'TEXTO-DO-CV'): void
    {
        Http::fake([
            'analisecv.test/*' => Http::response(['ok' => true, 'text' => $texto, 'vector' => [0.1], 'model' => 'LaBSE']),
            'api.jev.test/*' => Http::response([
                'model' => 'jev-1.13.0',
                'answers' => ['compatibilidade' => ['type' => 'noul', 'noul' => $noul]],
                'usage' => ['input_tokens' => 200, 'output_tokens' => 10],
            ]),
        ]);
    }

    private function verificar(Job $vaga, ?UploadedFile $cv = null)
    {
        return $this->post(
            route('cv-check', $vaga->slug),
            ['cv' => $cv ?? $this->pdf()],
            ['Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest']
        );
    }

    public function test_anyone_can_check_a_cv_against_a_job_without_an_account()
    {
        $this->fingir(0.83);
        $vaga = Job::factory()->create();

        $this->verificar($vaga)
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('percentagem', 83)
            ->assertJsonPath('nivel', 'alta');
    }

    /**
     * A descrição contra a qual se mede vem da base de dados, e não do pedido.
     * Se viesse do browser, qualquer pessoa podia mandar uma descrição feita à
     * medida do seu currículo e ver 100%.
     */
    public function test_the_job_text_comes_from_the_database_not_from_the_request()
    {
        $this->fingir();
        $vaga = Job::factory()->create([
            'title' => 'Motorista de Pesados',
            'description' => '<p>Carta de condução C+E e cinco anos de estrada.</p>',
        ]);

        $this->post(
            route('cv-check', $vaga->slug),
            ['cv' => $this->pdf(), 'description' => 'VAGA-FALSA-FEITA-A-MEDIDA'],
            ['Accept' => 'application/json']
        )->assertOk();

        Http::assertSent(function ($pedido) {
            if (!str_contains($pedido->url(), 'api.jev.test')) {
                return false;
            }

            $vagaEnviada = $pedido->data()['state']['vaga'] ?? '';

            return str_contains($vagaEnviada, 'Motorista de Pesados')
                && str_contains($vagaEnviada, 'Carta de condução C+E')
                && !str_contains($vagaEnviada, 'VAGA-FALSA-FEITA-A-MEDIDA');
        });
    }

    /** O HTML da descrição não deve chegar ao modelo como HTML. */
    public function test_the_job_description_reaches_jev_as_plain_text()
    {
        $this->fingir();
        $vaga = Job::factory()->create([
            'description' => '<p>Requisitos:</p><ul><li>Licenciatura</li><li>Inglês</li></ul>',
        ]);

        $this->verificar($vaga)->assertOk();

        Http::assertSent(function ($pedido) {
            if (!str_contains($pedido->url(), 'api.jev.test')) {
                return false;
            }

            $texto = $pedido->data()['state']['vaga'] ?? '';

            return !str_contains($texto, '<li>')
                && str_contains($texto, 'Licenciatura')
                // As etiquetas de bloco viram quebras de linha em vez de colar
                // as palavras: "LicenciaturaInglês" seria ilegível.
                && !str_contains($texto, 'LicenciaturaInglês');
        });
    }

    /**
     * @dataProvider faixas
     */
    public function test_each_band_gets_its_own_reading(float $noul, int $percentagem, string $nivel)
    {
        $this->fingir($noul);
        $vaga = Job::factory()->create();

        $resposta = $this->verificar($vaga)->assertOk();

        $this->assertSame($percentagem, $resposta->json('percentagem'));
        $this->assertSame($nivel, $resposta->json('nivel'));
        $this->assertNotSame('', trim((string) $resposta->json('leitura')));
    }

    public static function faixas(): array
    {
        return [
            'alta' => [0.91, 91, 'alta'],
            'no limite de cima' => [0.70, 70, 'alta'],
            'média' => [0.55, 55, 'media'],
            'no limite de baixo' => [0.40, 40, 'media'],
            'baixa' => [0.12, 12, 'baixa'],
        ];
    }

    /**
     * Uma percentagem baixa nunca diz a ninguém para não se candidatar. Quem
     * escolhe é a empresa, e um CV mal escrito não é a mesma coisa que uma
     * pessoa que não serve para o lugar.
     */
    public function test_a_low_score_does_not_tell_the_candidate_to_give_up()
    {
        $this->fingir(0.08);
        $vaga = Job::factory()->create();

        $leitura = $this->verificar($vaga)->assertOk()->json('leitura');

        $this->assertStringContainsString('Pode candidatar-se', $leitura);
    }

    public function test_a_failure_is_an_error_and_never_a_zero_percent()
    {
        Http::fake([
            'analisecv.test/*' => Http::response(['ok' => true, 'text' => 'CV', 'vector' => [0.1], 'model' => 'LaBSE']),
            'api.jev.test/*' => Http::response(['erro' => 'x'], 500),
        ]);

        $this->verificar(Job::factory()->create())
            ->assertStatus(502)
            ->assertJsonPath('ok', false);
    }

    public function test_a_cv_that_cannot_be_read_says_so()
    {
        Http::fake([
            'analisecv.test/*' => Http::response(['ok' => true, 'text' => '   ', 'vector' => [0.1], 'model' => 'LaBSE']),
        ]);

        $this->verificar(Job::factory()->create())->assertStatus(502);
    }

    public function test_only_pdfs_are_accepted()
    {
        $this->fingir();

        $this->verificar(
            Job::factory()->create(),
            UploadedFile::fake()->createWithContent('cv.docx', 'nao e um pdf')
        )->assertStatus(422);

        Http::assertNothingSent();
    }

    public function test_a_job_that_does_not_exist_is_a_404()
    {
        $this->fingir();

        $this->post(
            route('cv-check', 'vaga-que-nao-existe'),
            ['cv' => $this->pdf()],
            ['Accept' => 'application/json']
        )->assertNotFound();
    }

    /**
     * Com o JEV desligado não há percentagem única para mostrar, por isso a
     * secção não aparece na página — e a rota também não responde, caso alguém
     * a chame à mão.
     */
    public function test_with_jev_off_the_section_is_gone_and_the_route_refuses()
    {
        $vaga = Job::factory()->create();

        $this->get('/vagas/' . $vaga->slug)
            ->assertOk()
            ->assertSee('id="verificar-cv"', false);

        config(['services.cv_analyzer_engine' => 'vectores']);

        $this->get('/vagas/' . $vaga->slug)
            ->assertOk()
            ->assertDontSee('id="verificar-cv"', false)
            ->assertDontSee('verificar-cv.js', false);

        $this->verificar($vaga)->assertStatus(503);
    }

    public function test_the_section_appears_on_the_job_page_with_its_upload()
    {
        $vaga = Job::factory()->create();

        $this->get('/vagas/' . $vaga->slug)
            ->assertOk()
            ->assertSee('id="verificar-cv"', false)
            ->assertSee('Verifique o seu CV')
            ->assertSee('Arraste o seu CV para aqui')
            ->assertSee(route('cv-check', $vaga->slug), false)
            ->assertSee('verificar-cv.js', false);
    }

    public function test_the_section_follows_the_language_of_the_page()
    {
        $vaga = Job::factory()->create();

        $this->get(route('locale.switch', 'en'));

        $this->get('/vagas/' . $vaga->slug)
            ->assertOk()
            ->assertSee('Check your CV')
            ->assertSee('Drag your CV here');
    }
}

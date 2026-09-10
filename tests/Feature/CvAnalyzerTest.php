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

    /**
     * Vector alinhado (1.0) ou oposto (-1.0) ao da vaga, para tornar a semelhança
     * de coseno determinística no teste.
     */
    private function vector(float $direction): array
    {
        return array_fill(0, VectorSimilarity::DIMENSIONS, $direction);
    }
}

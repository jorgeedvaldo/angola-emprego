<?php

namespace Tests\Feature;

use App\Models\CvScreening;
use App\Models\CvScreeningCandidate;
use App\Models\User;
use App\Support\VectorSimilarity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CvScreeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_recruiters_hub_lists_the_three_options()
    {
        $this->get(route('recruiters.index'))
            ->assertOk()
            ->assertSee('Criar empresa')
            ->assertSee('Lista de empresas')
            ->assertSee('Analisar CV');
    }

    public function test_guest_cannot_open_the_cv_analyzer()
    {
        $this->get(route('cv-screenings.index'))->assertRedirect(route('login'));
    }

    public function test_recruiter_can_create_a_screening_with_several_cvs()
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('cv-screenings.store'), [
            'title' => 'Técnico de Manutenção',
            'description' => 'Procuramos técnico com experiência em manutenção industrial.',
            'cvs' => [
                UploadedFile::fake()->create('joao.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->create('maria.pdf', 100, 'application/pdf'),
            ],
        ]);

        $screening = CvScreening::firstOrFail();

        $response->assertRedirect(route('cv-screenings.show', $screening));
        $this->assertSame($user->id, $screening->user_id);
        $this->assertCount(2, $screening->candidates);

        foreach ($screening->candidates as $candidate) {
            Storage::disk('local')->assertExists($candidate->path);
        }
    }

    public function test_only_pdf_cvs_are_accepted()
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('cv-screenings.store'), [
                'title' => 'Contabilista',
                'description' => 'Vaga para contabilista sénior.',
                'cvs' => [UploadedFile::fake()->create('cv.docx', 100)],
            ])
            ->assertSessionHasErrors('cvs.0');

        $this->assertSame(0, CvScreening::count());
    }

    public function test_candidates_are_listed_from_most_to_least_compatible()
    {
        $user = User::factory()->create();

        $screening = CvScreening::create([
            'user_id' => $user->id,
            'title' => 'Soldador',
            'description' => 'Soldadura industrial com certificação.',
            'description_vector' => $this->vector(1.0),
            'description_vector_model' => VectorSimilarity::MODEL_ID,
            'description_vector_generated_at' => now(),
        ]);

        $weak = $this->candidate($screening, 'fraco.pdf', 'Motorista de pesados.', -1.0);
        $strong = $this->candidate($screening, 'forte.pdf', 'Soldadura industrial com certificação.', 1.0);

        $response = $this->actingAs($user)->get(route('cv-screenings.show', $screening));

        $response->assertOk();
        $this->assertLessThan(
            strpos($response->getContent(), $weak->original_name),
            strpos($response->getContent(), $strong->original_name),
            'O CV mais compatível deve aparecer primeiro.'
        );
    }

    public function test_analysis_endpoints_store_the_vectors_returned_by_the_service()
    {
        config([
            'services.analisecv.url' => 'https://analisecv.test',
            'services.analisecv.api_key' => 'chave',
        ]);

        Storage::fake('local');
        Http::fake([
            '*/embed' => Http::response(['ok' => true, 'vector' => $this->vector(1.0), 'model' => VectorSimilarity::MODEL_ID]),
            '*/analyze-cv' => Http::response([
                'ok' => true,
                'text' => 'Texto do CV',
                'vector' => $this->vector(1.0),
                'model' => VectorSimilarity::MODEL_ID,
            ]),
        ]);

        $user = User::factory()->create();
        $screening = CvScreening::create([
            'user_id' => $user->id,
            'title' => 'Electricista',
            'description' => 'Instalações eléctricas industriais.',
        ]);

        Storage::disk('local')->put($screening->storageDirectory() . '/cv.pdf', 'conteudo');
        $candidate = CvScreeningCandidate::create([
            'cv_screening_id' => $screening->id,
            'path' => $screening->storageDirectory() . '/cv.pdf',
            'original_name' => 'cv.pdf',
        ]);

        $this->actingAs($user)
            ->post(route('cv-screenings.analyze', $screening))
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->actingAs($user)
            ->post(route('cv-screenings.cvs.analyze', [$screening, $candidate]))
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertTrue($screening->fresh()->hasCurrentVector());
        $this->assertTrue($candidate->fresh()->hasCurrentVector());
        $this->assertSame('Texto do CV', $candidate->fresh()->cv_text);
    }

    public function test_a_recruiter_cannot_open_another_recruiters_screening()
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $screening = CvScreening::create([
            'user_id' => $owner->id,
            'title' => 'Gestor de Stock',
            'description' => 'Gestão de armazém.',
        ]);

        $this->actingAs($intruder)->get(route('cv-screenings.show', $screening))->assertForbidden();
        $this->actingAs($intruder)->delete(route('cv-screenings.destroy', $screening))->assertForbidden();
    }

    public function test_removing_a_screening_deletes_the_uploaded_cvs()
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('cv-screenings.store'), [
            'title' => 'Recepcionista',
            'description' => 'Atendimento ao público.',
            'cvs' => [UploadedFile::fake()->create('cv.pdf', 50, 'application/pdf')],
        ]);

        $screening = CvScreening::firstOrFail();
        $path = $screening->candidates()->firstOrFail()->path;

        $this->actingAs($user)
            ->delete(route('cv-screenings.destroy', $screening))
            ->assertRedirect(route('cv-screenings.index'));

        Storage::disk('local')->assertMissing($path);
        $this->assertSame(0, CvScreening::count());
        $this->assertSame(0, CvScreeningCandidate::count());
    }

    /**
     * Vector alinhado (1.0) ou oposto (-1.0) ao da descrição, para tornar a
     * ordenação por semelhança de coseno determinística no teste.
     */
    private function vector(float $direction): array
    {
        return array_fill(0, VectorSimilarity::DIMENSIONS, $direction);
    }

    private function candidate(CvScreening $screening, string $name, string $text, float $direction): CvScreeningCandidate
    {
        return CvScreeningCandidate::create([
            'cv_screening_id' => $screening->id,
            'path' => $screening->storageDirectory() . '/' . $name,
            'original_name' => $name,
            'cv_text' => $text,
            'cv_vector' => $this->vector($direction),
            'cv_vector_model' => VectorSimilarity::MODEL_ID,
            'cv_analyzed_at' => now(),
        ]);
    }
}

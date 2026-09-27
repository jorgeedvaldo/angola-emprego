<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * DELETE /api/jobs/{id}.
 *
 * É a única rota desta API que não se desfaz, e a única que pede chave: o
 * /api/job/create está aberto ao mundo, e publicar uma vaga a mais é chato mas
 * desfaz-se — apagar não.
 */
class ApiJobDeleteTest extends TestCase
{
    use RefreshDatabase;

    private const CHAVE = 'chave-de-teste-comprida-o-suficiente';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.api.key' => self::CHAVE]);
    }

    private function apagar(int $id, array $corpo = [], ?string $chave = self::CHAVE)
    {
        $cabecalhos = ['Accept' => 'application/json'];

        if ($chave !== null) {
            $cabecalhos['X-API-Key'] = $chave;
        }

        return $this->json('DELETE', "/api/jobs/$id", $corpo, $cabecalhos);
    }

    public function test_it_deletes_a_job()
    {
        $vaga = Job::factory()->create(['title' => 'Vaga a apagar']);

        $this->apagar($vaga->id)
            ->assertOk()
            ->assertJsonPath('deleted.id', $vaga->id)
            ->assertJsonPath('deleted.title', 'Vaga a apagar');

        $this->assertDatabaseMissing('jobs', ['id' => $vaga->id]);
    }

    // ---------------------------------------------------------------- chave

    public function test_without_a_key_nothing_is_deleted()
    {
        $vaga = Job::factory()->create();

        $this->apagar($vaga->id, [], null)->assertStatus(401);

        $this->assertDatabaseHas('jobs', ['id' => $vaga->id]);
    }

    public function test_with_the_wrong_key_nothing_is_deleted()
    {
        $vaga = Job::factory()->create();

        $this->apagar($vaga->id, [], 'chave-errada')->assertStatus(401);

        $this->assertDatabaseHas('jobs', ['id' => $vaga->id]);
    }

    /**
     * Sem chave configurada no servidor a rota fecha-se a toda a gente. Ficar
     * aberta por esquecimento seria o pior dos dois mundos.
     */
    public function test_with_no_key_configured_the_route_is_closed_to_everyone()
    {
        config(['services.api.key' => null]);
        $vaga = Job::factory()->create();

        $this->apagar($vaga->id)->assertStatus(503);
        $this->apagar($vaga->id, [], null)->assertStatus(503);

        $this->assertDatabaseHas('jobs', ['id' => $vaga->id]);
    }

    // ------------------------------------------------ o que está agarrado

    /**
     * O category_jobs tem chave estrangeira sem cascata. Uma vaga com
     * categorias, apagada sem as soltar antes, rebenta em MySQL — foi por isto
     * que este endpoint não é um $job->delete().
     */
    public function test_a_job_with_categories_is_deleted_and_the_pivot_goes_with_it()
    {
        $vaga = Job::factory()->create();
        $categorias = collect(['Informática', 'Gestão'])
            ->map(fn ($nome) => Category::create(['name' => $nome]));

        $vaga->categories()->sync($categorias->pluck('id')->all());
        $this->assertDatabaseCount('category_jobs', 2);

        $this->apagar($vaga->id)->assertOk();

        $this->assertDatabaseMissing('jobs', ['id' => $vaga->id]);
        $this->assertDatabaseCount('category_jobs', 0);

        // As categorias em si não são da vaga e continuam lá.
        $this->assertDatabaseCount('categories', 2);
    }

    public function test_the_cover_and_its_thumbnail_leave_the_disk()
    {
        Storage::fake('public');
        Storage::fake('local');

        $vaga = Job::factory()->create(['image' => 'images/jobs/capa.png']);
        Storage::disk('public')->put('images/jobs/capa.png', 'imagem');
        Storage::disk('public')->put('thumb/images/jobs/capa.png', 'miniatura');

        $this->apagar($vaga->id)->assertOk()->assertJsonPath('deleted.files_removed', 2);

        Storage::disk('public')->assertMissing('images/jobs/capa.png');
        Storage::disk('public')->assertMissing('thumb/images/jobs/capa.png');
    }

    /**
     * As candidaturas descem em cascata pela base de dados, mas os CVs que as
     * pessoas enviaram ficariam no disco para sempre.
     */
    public function test_the_cvs_that_candidates_sent_leave_the_disk_too()
    {
        Storage::fake('public');
        Storage::fake('local');

        $vaga = Job::factory()->create(['image' => null]);
        $this->candidatura($vaga);

        Storage::disk('local')->put('job-applications/' . $vaga->id . '/cv.pdf', '%PDF');
        Storage::disk('local')->assertExists('job-applications/' . $vaga->id . '/cv.pdf');

        $this->apagar($vaga->id, ['force' => true])->assertOk();

        Storage::disk('local')->assertMissing('job-applications/' . $vaga->id . '/cv.pdf');
        $this->assertDatabaseCount('job_applications', 0);
    }

    // -------------------------------------------------------- candidaturas

    /**
     * Uma vaga com candidaturas não se apaga por engano: são CVs que pessoas
     * enviaram e que desaparecem com ela.
     */
    public function test_a_job_with_applications_is_not_deleted_by_accident()
    {
        $vaga = Job::factory()->create();
        $this->candidatura($vaga);

        $this->apagar($vaga->id)
            ->assertStatus(409)
            ->assertJsonPath('applications', 1);

        $this->assertDatabaseHas('jobs', ['id' => $vaga->id]);
        $this->assertDatabaseCount('job_applications', 1);
    }

    public function test_with_force_the_applications_go_with_the_job()
    {
        $vaga = Job::factory()->create();
        $this->candidatura($vaga);

        $this->apagar($vaga->id, ['force' => true])
            ->assertOk()
            ->assertJsonPath('deleted.applications', 1);

        $this->assertDatabaseMissing('jobs', ['id' => $vaga->id]);
        $this->assertDatabaseCount('job_applications', 0);
    }

    // -------------------------------------------------------------- o resto

    public function test_a_job_that_does_not_exist_is_a_404()
    {
        $this->apagar(999999)->assertStatus(404);
    }

    /** Apagar duas vezes não pode dar erro de servidor na segunda. */
    public function test_deleting_twice_is_a_404_the_second_time()
    {
        $vaga = Job::factory()->create();

        $this->apagar($vaga->id)->assertOk();
        $this->apagar($vaga->id)->assertStatus(404);
    }

    /** As outras rotas da API não passam a pedir chave por causa desta. */
    public function test_creating_a_job_still_works_without_a_key()
    {
        $this->postJson('/api/job/create', [
            'title' => 'Motorista',
            'company' => 'Transportes Lda',
            'location' => 'Luanda',
            'description' => '<p>Descrição</p>',
            'email_or_link' => 'rh@exemplo.ao',
        ])->assertOk();
    }

    private function candidatura(Job $vaga): JobApplication
    {
        return JobApplication::create([
            'job_id' => $vaga->id,
            'name' => 'Ana Silva',
            'email' => 'ana@exemplo.ao',
            'subject' => 'Candidatura',
            'message' => 'Junto envio o meu CV.',
            'attachment_path' => 'job-applications/' . $vaga->id . '/cv.pdf',
            'attachment_name' => 'cv.pdf',
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompanyJobsTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_can_register_and_get_public_page()
    {
        $response = $this->post('/registar-empresa', [
            'company_name' => 'Minha Empresa',
            'slug' => 'minha-empresa',
            'name' => 'Ana Silva',
            'mobile' => '923000000',
            'email' => 'empresa@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'location' => 'Luanda',
        ]);

        $response->assertRedirect(route('company.dashboard'));
        $this->assertDatabaseHas('companies', [
            'slug' => 'minha-empresa',
            'name' => 'Minha Empresa',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'empresa@example.com',
            'account_type' => 'company',
        ]);

        $this->get('/company/minha-empresa')
            ->assertOk()
            ->assertSee('Minha Empresa');
    }

    public function test_company_can_publish_a_job()
    {
        $company = Company::factory()->create([
            'name' => 'Minha Empresa',
            'slug' => 'minha-empresa',
        ]);

        $this->actingAs($company->user)
            ->post(route('company.jobs.store'), [
                'title' => 'Desenvolvedor Laravel',
                'location' => 'Luanda',
                'description' => 'Procuramos um desenvolvedor com experiência em Laravel.',
            ])
            ->assertRedirect(route('company.dashboard'));

        $this->assertDatabaseHas('jobs', [
            'company_id' => $company->id,
            'title' => 'Desenvolvedor Laravel',
            'company' => 'Minha Empresa',
        ]);

        $job = Job::where('title', 'Desenvolvedor Laravel')->first();

        $this->get('/company/minha-empresa')
            ->assertOk()
            ->assertSee('Desenvolvedor Laravel');

        $this->get('/vagas/' . $job->slug)
            ->assertOk()
            ->assertSee('Enviar candidatura')
            ->assertSee('Assunto');
    }

    public function test_candidate_can_apply_with_subject_message_and_cv()
    {
        Storage::fake('local');

        $company = Company::factory()->create(['slug' => 'minha-empresa']);
        $job = Job::factory()->create([
            'company_id' => $company->id,
            'company' => $company->name,
            'title' => 'Analista de RH',
        ]);

        $file = UploadedFile::fake()->create('cv.pdf', 120, 'application/pdf');

        $this->post(route('jobs.apply', $job->slug), [
            'name' => 'João Candidato',
            'email' => 'joao@example.com',
            'phone' => '912345678',
            'subject' => 'Candidatura a Analista de RH',
            'message' => 'Gostaria de me candidatar a esta vaga.',
            'attachment' => $file,
        ])->assertRedirect();

        $this->assertDatabaseHas('job_applications', [
            'job_id' => $job->id,
            'email' => 'joao@example.com',
            'subject' => 'Candidatura a Analista de RH',
        ]);

        $application = JobApplication::first();
        Storage::disk('local')->assertExists($application->attachment_path);

        $this->actingAs($company->user)
            ->get(route('company.jobs.applications', $job))
            ->assertOk()
            ->assertSee('João Candidato')
            ->assertSee('Candidatura a Analista de RH');
    }

    public function test_candidate_cannot_open_company_dashboard()
    {
        $user = User::factory()->create(['account_type' => 'candidate']);

        $this->actingAs($user)
            ->get(route('company.dashboard'))
            ->assertStatus(404)
            ->assertDontSee('Publicar vaga');
    }

    public function test_legacy_jobs_keep_email_apply_button()
    {
        $job = Job::factory()->create([
            'company_id' => null,
            'email_or_link' => 'rh@empresa.ao',
        ]);

        $this->get('/vagas/' . $job->slug)
            ->assertOk()
            ->assertDontSee('Enviar candidatura')
            ->assertSee('mailto:rh@empresa.ao');
    }
}

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

    public function test_company_can_configure_a_public_careers_page()
    {
        $company = Company::factory()->create([
            'name' => 'Minha Empresa',
            'slug' => 'minha-empresa',
            'max_attachments' => 1,
        ]);

        $this->actingAs($company->user)
            ->put(route('company.update'), [
                'name' => 'Minha Empresa',
                'slug' => 'minha-empresa',
                'headline' => 'Construa o futuro connosco',
                'description' => 'Somos uma empresa angolana focada em tecnologia e pessoas.',
                'location' => 'Luanda',
                'website' => 'https://example.com',
                'linkedin_url' => 'https://www.linkedin.com/company/minha-empresa',
                'facebook_url' => 'https://www.facebook.com/minhaempresa',
                'instagram_url' => 'https://www.instagram.com/minhaempresa',
                'max_attachments' => 2,
            ])
            ->assertRedirect();

        Job::factory()->create([
            'company_id' => $company->id,
            'company' => $company->name,
            'title' => 'Engenheiro de Software',
        ]);

        $this->get('/company/minha-empresa')
            ->assertOk()
            ->assertSee('Construa o futuro connosco')
            ->assertSee('Sobre a Minha Empresa')
            ->assertSee('Somos uma empresa angolana focada em tecnologia e pessoas.')
            ->assertSee('Engenheiro de Software')
            ->assertSee('https://www.linkedin.com/company/minha-empresa', false)
            ->assertSee('https://www.facebook.com/minhaempresa', false)
            ->assertSee('https://www.instagram.com/minhaempresa', false);
    }

    public function test_public_company_pages_drop_the_marketplace_chrome()
    {
        $company = Company::factory()->create([
            'name' => 'Minha Empresa',
            'slug' => 'minha-empresa',
            'headline' => 'Construa o futuro connosco',
            'linkedin_url' => 'https://www.linkedin.com/company/minha-empresa',
            'instagram_url' => 'https://www.instagram.com/minhaempresa',
        ]);

        $job = Job::factory()->create([
            'company_id' => $company->id,
            'company' => $company->name,
            'title' => 'Engenheiro de Software',
        ]);

        $careersPage = $this->get(route('companies.show', $company->slug))->assertOk();

        $careersPage
            ->assertDontSee('Angola Emprego')
            ->assertDontSee('assets/img/logo.svg')
            ->assertDontSee('assets/img/favicon.png')
            ->assertDontSee(route('courses.index'))
            ->assertSee('Carreiras')
            ->assertSee('company-nav')
            ->assertSee('https://www.linkedin.com/company/minha-empresa', false)
            ->assertSee(route('companies.job', [$company->slug, $job->slug]), false);

        $this->get(route('companies.job', [$company->slug, $job->slug]))
            ->assertOk()
            ->assertDontSee('Angola Emprego')
            ->assertSee('Engenheiro de Software')
            ->assertSee('Enviar candidatura')
            ->assertSee('Assunto')
            ->assertSee($company->name);
    }

    public function test_company_job_page_is_scoped_to_its_own_company()
    {
        $company = Company::factory()->create(['slug' => 'minha-empresa']);
        $otherCompany = Company::factory()->create(['slug' => 'outra-empresa']);

        $job = Job::factory()->create([
            'company_id' => $otherCompany->id,
            'company' => $otherCompany->name,
        ]);

        $this->get(route('companies.job', ['minha-empresa', $job->slug]))
            ->assertNotFound();
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
            'attachments' => [$file],
        ])->assertRedirect();

        $this->assertDatabaseHas('job_applications', [
            'job_id' => $job->id,
            'email' => 'joao@example.com',
            'subject' => 'Candidatura a Analista de RH',
        ]);

        $application = JobApplication::first();
        Storage::disk('local')->assertExists($application->attachment_path);
        $this->assertDatabaseCount('job_application_attachments', 1);

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

    public function test_company_can_set_max_attachments_and_candidates_must_respect_it()
    {
        Storage::fake('local');

        $company = Company::factory()->create([
            'slug' => 'minha-empresa',
            'max_attachments' => 1,
        ]);

        $this->actingAs($company->user)
            ->put(route('company.update'), [
                'name' => $company->name,
                'slug' => $company->slug,
                'max_attachments' => 3,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'max_attachments' => 3,
        ]);

        $job = Job::factory()->create([
            'company_id' => $company->id,
            'company' => $company->name,
        ]);

        $this->get('/vagas/' . $job->slug)
            ->assertOk()
            ->assertSee('até')
            ->assertSee('3');

        $tooMany = [
            UploadedFile::fake()->create('cv.pdf', 80, 'application/pdf'),
            UploadedFile::fake()->create('certificado.pdf', 80, 'application/pdf'),
            UploadedFile::fake()->create('carta.pdf', 80, 'application/pdf'),
            UploadedFile::fake()->create('extra.pdf', 80, 'application/pdf'),
        ];

        $this->post(route('jobs.apply', $job->slug), [
            'name' => 'João Candidato',
            'email' => 'joao2@example.com',
            'subject' => 'Candidatura',
            'message' => 'Segue o meu CV e documentos.',
            'attachments' => $tooMany,
        ])->assertSessionHasErrors('attachments');

        $allowed = [
            UploadedFile::fake()->create('cv.pdf', 80, 'application/pdf'),
            UploadedFile::fake()->create('certificado.pdf', 80, 'application/pdf'),
            UploadedFile::fake()->create('carta.pdf', 80, 'application/pdf'),
        ];

        $this->post(route('jobs.apply', $job->slug), [
            'name' => 'João Candidato',
            'email' => 'joao2@example.com',
            'subject' => 'Candidatura',
            'message' => 'Segue o meu CV e documentos.',
            'attachments' => $allowed,
        ])->assertRedirect();

        $this->assertDatabaseCount('job_application_attachments', 3);

        $this->actingAs($company->user)
            ->get(route('company.jobs.applications', $job))
            ->assertOk()
            ->assertSee('cv.pdf')
            ->assertSee('certificado.pdf')
            ->assertSee('carta.pdf');
    }
}

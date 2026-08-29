<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Job;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CompanyAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.maileroo.api_key' => 'test-sending-key',
            'services.maileroo.endpoint' => 'https://smtp.maileroo.com/api/v2/emails',
            'services.maileroo.from_address' => 'no-reply@angolaemprego.com',
            'services.maileroo.from_name' => 'Angola Emprego',
        ]);
    }

    public function test_company_registration_sends_verification_through_maileroo()
    {
        Http::fake([
            'smtp.maileroo.com/*' => Http::response(['success' => true], 200),
        ]);

        $this->post(route('register.company'), [
            'company_name' => 'Empresa Pendente',
            'slug' => 'empresa-pendente',
            'name' => 'Responsável',
            'mobile' => '923000100',
            'email' => 'pendente@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'theme_color' => '#2557A7',
        ])->assertRedirect(route('company.dashboard'));

        Http::assertSent(function ($request) {
            return $request->url() === 'https://smtp.maileroo.com/api/v2/emails'
                && $request->hasHeader('Authorization', 'Bearer test-sending-key')
                && $request['to'][0]['address'] === 'pendente@example.com'
                && $request['subject'] === 'Confirme o email da sua empresa'
                && str_contains($request['html'], '/email/verificar/');
        });
    }

    public function test_signed_link_verifies_company_email()
    {
        $company = Company::factory()->create([
            'approval_status' => 'pending',
            'approved_at' => null,
        ]);
        $user = $company->user;
        $user->forceFill(['email_verified_at' => null])->save();

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)
            ->get($url)
            ->assertRedirect(route('company.dashboard'));

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_pending_company_is_not_public_and_cannot_publish()
    {
        $company = Company::factory()->create([
            'approval_status' => 'pending',
            'approved_at' => null,
        ]);

        $this->get(route('companies.show', $company->slug))->assertNotFound();

        $this->actingAs($company->user)
            ->get(route('company.jobs.create'))
            ->assertRedirect(route('company.dashboard'))
            ->assertSessionHas('error');
    }

    public function test_existing_jobs_are_hidden_when_company_is_not_approved()
    {
        $company = Company::factory()->create([
            'approval_status' => 'pending',
            'approved_at' => null,
        ]);
        $job = Job::factory()->create([
            'company_id' => $company->id,
            'company' => $company->name,
            'title' => 'Vaga ainda privada',
        ]);

        $this->get('/vagas')->assertDontSee('Vaga ainda privada');
        $this->get('/vagas/' . $job->slug)->assertNotFound();
        $this->post(route('jobs.apply', $job->slug), [])->assertNotFound();
    }

    public function test_unverified_approved_company_is_not_public_and_cannot_publish()
    {
        $company = Company::factory()->create(['approval_status' => 'approved']);
        $company->user->forceFill(['email_verified_at' => null])->save();

        $this->get(route('companies.show', $company->slug))->assertNotFound();

        $this->actingAs($company->user)
            ->get(route('company.jobs.create'))
            ->assertRedirect(route('company.dashboard'))
            ->assertSessionHas('error');
    }

    public function test_approval_sets_audit_fields_and_makes_verified_company_public()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $company = Company::factory()->create([
            'approval_status' => 'pending',
            'approved_at' => null,
            'approved_by' => null,
        ]);

        $this->actingAs($admin);
        $company->update(['approval_status' => 'approved']);

        $company->refresh();
        $this->assertNotNull($company->approved_at);
        $this->assertSame($admin->id, $company->approved_by);
        $this->get(route('companies.show', $company->slug))->assertOk();
    }

    public function test_password_reset_email_is_sent_through_maileroo()
    {
        Http::fake([
            'smtp.maileroo.com/*' => Http::response(['success' => true], 200),
        ]);
        $user = User::factory()->create(['email' => 'empresa@example.com']);

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status');

        Http::assertSent(function ($request) use ($user) {
            return $request['to'][0]['address'] === $user->email
                && $request['subject'] === 'Redefinir a sua senha'
                && str_contains($request['html'], '/redefinir-senha/');
        });
    }

    public function test_password_can_be_reset_with_a_valid_token()
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }
}

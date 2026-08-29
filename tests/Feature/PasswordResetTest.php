<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
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

    public function test_forgot_password_page_is_available_for_candidates_and_companies()
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Candidatos e empresas')
            ->assertSee('nome@exemplo.com', false);
    }

    public function test_candidate_password_reset_email_is_sent_through_maileroo()
    {
        Http::fake([
            'smtp.maileroo.com/*' => Http::response(['success' => true], 200),
        ]);

        $user = User::factory()->create([
            'email' => 'candidato@example.com',
            'account_type' => 'candidate',
        ]);

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status');

        Http::assertSent(function ($request) use ($user) {
            return $request->url() === 'https://smtp.maileroo.com/api/v2/emails'
                && $request['to'][0]['address'] === $user->email
                && $request['subject'] === 'Redefinir a sua senha'
                && str_contains($request['html'], 'conta de candidato')
                && str_contains($request['html'], '/redefinir-senha/')
                && ($request['tags']['account_type'] ?? null) === 'candidate';
        });
    }

    public function test_company_password_reset_email_is_sent_through_maileroo()
    {
        Http::fake([
            'smtp.maileroo.com/*' => Http::response(['success' => true], 200),
        ]);

        $company = Company::factory()->create();
        $user = $company->user;

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status');

        Http::assertSent(function ($request) use ($user) {
            return $request['to'][0]['address'] === $user->email
                && str_contains($request['html'], 'conta de empresa')
                && ($request['tags']['account_type'] ?? null) === 'company';
        });
    }

    public function test_candidate_can_reset_password_and_is_signed_in()
    {
        $user = User::factory()->create([
            'account_type' => 'candidate',
            'password' => Hash::make('old-password'),
        ]);
        $token = Password::broker()->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user->fresh());
        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }

    public function test_company_can_reset_password_and_reaches_dashboard()
    {
        $company = Company::factory()->create();
        $user = $company->user;
        $token = Password::broker()->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect(route('company.dashboard'));

        $this->assertAuthenticatedAs($user->fresh());
        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }

    public function test_unknown_email_does_not_reveal_accounts()
    {
        Http::fake();

        $this->post(route('password.email'), ['email' => 'naoexiste@example.com'])
            ->assertSessionHas('status');

        Http::assertNothingSent();
    }
}

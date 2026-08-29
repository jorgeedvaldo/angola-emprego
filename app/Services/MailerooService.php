<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class MailerooService
{
    public function sendCompanyVerification(User $user): bool
    {
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        return $this->send(
            $user,
            'Confirme o email da sua empresa',
            view('emails.company-verification', compact('user', 'url'))->render(),
            "Confirme o email da sua empresa através deste link: {$url}",
            'company-email-verification'
        );
    }

    public function sendPasswordReset(User $user, string $token): bool
    {
        $url = route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);

        return $this->send(
            $user,
            'Redefinir a sua senha',
            view('emails.password-reset', compact('user', 'url'))->render(),
            "Redefina a sua senha através deste link: {$url}",
            'password-reset'
        );
    }

    private function send(User $user, string $subject, string $html, string $plain, string $tag): bool
    {
        $apiKey = config('services.maileroo.api_key');
        $fromAddress = config('services.maileroo.from_address');

        if (!$apiKey || !$fromAddress) {
            Log::warning('Maileroo email not sent because configuration is incomplete.', [
                'type' => $tag,
                'user_id' => $user->id,
            ]);

            return false;
        }

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->asJson()
                ->timeout(15)
                ->post(config('services.maileroo.endpoint'), [
                    'from' => [
                        'address' => $fromAddress,
                        'display_name' => config('services.maileroo.from_name'),
                    ],
                    'to' => [[
                        'address' => $user->email,
                        'display_name' => $user->name,
                    ]],
                    'subject' => $subject,
                    'html' => $html,
                    'plain' => $plain,
                    'tracking' => true,
                    'tags' => [
                        'type' => $tag,
                        'user_id' => (string) $user->id,
                        'account_type' => $user->account_type ?? 'candidate',
                    ],
                ]);

            if (!$response->successful() || $response->json('success') === false) {
                Log::error('Maileroo rejected an email.', [
                    'type' => $tag,
                    'user_id' => $user->id,
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $exception) {
            Log::error('Maileroo request failed.', [
                'type' => $tag,
                'user_id' => $user->id,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}

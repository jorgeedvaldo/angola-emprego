<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\MailerooService;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, int $id, string $hash)
    {
        $user = User::findOrFail($id);

        abort_unless(
            $request->user()->is($user)
                && hash_equals($hash, sha1($user->getEmailForVerification())),
            403
        );

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return redirect()->route($user->isCompany() ? 'company.dashboard' : 'home')
            ->with('success', 'Email confirmado com sucesso.');
    }

    public function resend(Request $request, MailerooService $maileroo)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return back()->with('success', 'O email já está confirmado.');
        }

        $sent = $maileroo->sendCompanyVerification($request->user());

        return back()->with(
            $sent ? 'success' : 'error',
            $sent
                ? 'Enviámos um novo link de confirmação.'
                : 'Não foi possível enviar o email. Confirme a configuração do Maileroo.'
        );
    }
}

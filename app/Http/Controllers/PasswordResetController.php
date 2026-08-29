<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\MailerooService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function requestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request, MailerooService $maileroo)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $token = Password::broker()->createToken($user);
            $maileroo->sendPasswordReset($user, $token);
        }

        return back()->with(
            'status',
            'Se existir uma conta com este email, receberá uma ligação para redefinir a senha.'
        );
    }

    public function resetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function reset(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $resetUser = null;

        $status = Password::broker()->reset(
            $validated,
            function (User $user, string $password) use (&$resetUser) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                $resetUser = $user;
            }
        );

        if ($status !== Password::PASSWORD_RESET || !$resetUser) {
            return back()->withErrors(['email' => __($status)]);
        }

        Auth::login($resetUser);
        $request->session()->regenerate();

        $destination = $resetUser->isCompany()
            ? route('company.dashboard')
            : route('home');

        return redirect($destination)->with('status', 'Senha redefinida. Já está autenticado.');
    }
}

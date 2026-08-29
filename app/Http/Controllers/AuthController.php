<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function showCompanyRegisterForm()
    {
        return view('auth.register-company');
    }

    public function registerCompany(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:80',
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'location' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
        ], [
            'slug.regex' => 'O URL da página deve conter apenas letras minúsculas, números e hífens.',
        ]);

        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'mobile' => $validated['mobile'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'account_type' => 'company',
        ]);

        $user->update([
            'username' => \App\Models\User::generateUsername($validated['name'], $user->id),
        ]);

        $slugSource = $validated['slug'] ?? $validated['company_name'];
        $slug = \App\Models\Company::generateUniqueSlug($slugSource);

        \App\Models\Company::create([
            'user_id' => $user->id,
            'name' => $validated['company_name'],
            'slug' => $slug,
            'email' => $validated['email'],
            'phone' => $validated['mobile'],
            'location' => $validated['location'] ?? null,
            'website' => $validated['website'] ?? null,
        ]);

        \Illuminate\Support\Facades\Auth::login($user);

        return redirect()->route('company.dashboard')->with('success', 'Empresa registada com sucesso. Complete o perfil e publique a primeira vaga.');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (\Illuminate\Support\Facades\Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = \Illuminate\Support\Facades\Auth::user();
            if ($user && $user->isCompany()) {
                return redirect()->intended(route('company.dashboard'));
            }

            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'As credenciais fornecidas não correspondem aos nossos registos.',
        ])->onlyInput('email');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sex' => 'required|string',
            'birth_date' => 'required|date',
            'mobile' => 'required|string|max:20',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'sex' => $validated['sex'],
            'birth_date' => $validated['birth_date'],
            'mobile' => $validated['mobile'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        ]);

        // Generate unique username from name
        $user->update([
            'username' => \App\Models\User::generateUsername($validated['name'], $user->id),
        ]);

        \Illuminate\Support\Facades\Auth::login($user);

        return redirect('/');
    }

    public function logout(Request $request)
    {
        \Illuminate\Support\Facades\Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}

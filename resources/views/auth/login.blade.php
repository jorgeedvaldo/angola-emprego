@extends('templates.app')

@section('title', __('site.auth.entrar'))
@section('description', __('site.auth.entrar_descricao'))

@section('content')
<div class="auth-wrapper d-flex align-items-center justify-content-center" style="min-height: 80vh; background-color: #f3f2f1;">
    <div class="card shadow-sm border-0" style="width: 100%; max-width: 450px; border-radius: 12px;">
        <div class="card-body p-5">
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            <div class="text-center mb-5">
                <img src="{{ asset('assets/img/logo.svg') }}" alt="Angola Emprego" height="40" class="mb-3">
                <h4 class="fw-bold text-dark">{{ __('site.auth.entrar') }}</h4>
                <p class="text-muted small">{{ __('site.auth.entrar_boas_vindas') }}</p>
            </div>

            <div class="d-grid gap-2 mb-3">
                <a href="{{ route('auth.google') }}" class="btn btn-white border fw-bold py-3 d-flex align-items-center justify-content-center gap-2 shadow-sm" style="border-radius: 8px;">
                     <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" width="20" height="20">
                     {{ __('site.auth.entrar_google') }}
                </a>
            </div>

            <div class="position-relative text-center mb-4">
                <span class="bg-white px-2 small text-muted position-relative z-1">{{ __('site.auth.ou_email') }}</span>
                <hr class="position-absolute w-100 top-50 start-0 z-0 my-0 border-muted opacity-25">
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-floating mb-3">
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="nome@exemplo.com" value="{{ old('email') }}" required autofocus style="border-radius: 8px;">
                    <label for="email">{{ __('site.auth.email') }}</label>
                    @error('email')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-floating mb-4">
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="{{ __('site.auth.senha') }}" required style="border-radius: 8px;">
                    <label for="password">{{ __('site.auth.senha') }}</label>
                    @error('password')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="text-end mb-3">
                    <a href="{{ route('password.request') }}" class="small text-decoration-none fw-semibold">{{ __('site.auth.esqueceu_senha') }}</a>
                </div>

                <div class="d-grid gap-2 mb-4">
                    <button type="submit" class="btn btn-primary fw-bold py-3" style="border-radius: 8px; background-color: #2557a7; border-color: #2557a7;">
                        {{ __('site.auth.entrar') }}
                    </button>


                </div>

                <div class="text-center">
                    <p class="small text-muted mb-1">{{ __('site.auth.novo_aqui') }} <a href="{{ route('register') }}" class="text-decoration-none fw-bold" style="color: #2557a7;">{{ __('site.auth.criar_conta_ligacao') }}</a></p>
                    <p class="small text-muted mb-0">{{ __('site.auth.e_empresa') }} <a href="{{ route('register.company') }}" class="text-decoration-none fw-bold" style="color: #2557a7;">{{ __('site.auth.registar_empresa_ligacao') }}</a></p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

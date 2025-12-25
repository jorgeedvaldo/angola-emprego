@extends('templates.app')

@section('title', 'Entrar')
@section('description', 'Entre na sua conta Angola Emprego')

@section('content')
<div class="auth-wrapper d-flex align-items-center justify-content-center" style="min-height: 80vh; background-color: #f3f2f1;">
    <div class="card shadow-sm border-0" style="width: 100%; max-width: 450px; border-radius: 12px;">
        <div class="card-body p-5">
            <div class="text-center mb-5">
                <img src="{{ asset('assets/img/logo.svg') }}" alt="Angola Emprego" height="40" class="mb-3">
                <h4 class="fw-bold text-dark">Entrar</h4>
                <p class="text-muted small">Bem-vindo de volta! Entre para continuar.</p>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-floating mb-3">
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="nome@exemplo.com" value="{{ old('email') }}" required autofocus style="border-radius: 8px;">
                    <label for="email">Email</label>
                    @error('email')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-floating mb-4">
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Senha" required style="border-radius: 8px;">
                    <label for="password">Senha</label>
                    @error('password')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="d-grid gap-2 mb-4">
                    <button type="submit" class="btn btn-primary fw-bold py-3" style="border-radius: 8px; background-color: #2557a7; border-color: #2557a7;">
                        Entrar
                    </button>

                    <div class="position-relative text-center my-3">
                        <span class="bg-white px-2 small text-muted position-relative z-1">Ou continue com</span>
                        <hr class="position-absolute w-100 top-50 start-0 z-0 my-0 border-muted opacity-25">
                    </div>

                    <a href="{{ route('auth.google') }}" class="btn btn-white border fw-bold py-3 d-flex align-items-center justify-content-center gap-2 shadow-sm" style="border-radius: 8px;">
                         <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" width="20" height="20">
                         Iniciar sessão com Google
                    </a>
                </div>

                <div class="text-center">
                    <p class="small text-muted mb-0">Novo no Angola Emprego? <a href="{{ route('register') }}" class="text-decoration-none fw-bold" style="color: #2557a7;">Criar conta</a></p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

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
                </div>

                <div class="text-center">
                    <p class="small text-muted mb-0">Novo no Angola Emprego? <a href="{{ route('register') }}" class="text-decoration-none fw-bold" style="color: #2557a7;">Criar conta</a></p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

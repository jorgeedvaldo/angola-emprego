@extends('templates.app')

@section('title', 'Recuperar senha')
@section('description', 'Receba uma ligação para redefinir a sua senha')

@section('content')
<div class="auth-wrapper d-flex align-items-center justify-content-center py-5" style="min-height: 80vh; background-color: #f3f2f1;">
    <div class="card shadow-sm border-0" style="width: 100%; max-width: 460px; border-radius: 12px;">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <img src="{{ asset('assets/img/logo.svg') }}" alt="Angola Emprego" height="40" class="mb-3">
                <h4 class="fw-bold text-dark">Recuperar senha</h4>
                <p class="text-muted small">Candidatos e empresas podem recuperar a senha com o email da conta. Enviaremos uma ligação segura.</p>
            </div>

            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="form-floating mb-4">
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="nome@exemplo.com" value="{{ old('email') }}" required autofocus>
                    <label for="email">Email</label>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn btn-primary fw-bold py-3 w-100" style="background-color:#2557a7;border-color:#2557a7;">
                    Enviar ligação
                </button>
            </form>

            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="text-decoration-none fw-bold">Voltar ao login</a>
            </div>
        </div>
    </div>
</div>
@endsection

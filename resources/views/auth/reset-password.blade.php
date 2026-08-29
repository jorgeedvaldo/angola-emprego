@extends('templates.app')

@section('title', 'Redefinir senha')
@section('description', 'Defina uma nova senha para a sua conta')

@section('content')
<div class="auth-wrapper d-flex align-items-center justify-content-center py-5" style="min-height: 80vh; background-color: #f3f2f1;">
    <div class="card shadow-sm border-0" style="width: 100%; max-width: 500px; border-radius: 12px;">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <img src="{{ asset('assets/img/logo.svg') }}" alt="Angola Emprego" height="40" class="mb-3">
                <h4 class="fw-bold text-dark">Criar nova senha</h4>
            </div>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-floating mb-3">
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $email) }}" placeholder="Email" required>
                    <label for="email">Email</label>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-floating mb-3">
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Nova senha" required>
                    <label for="password">Nova senha</label>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirmar senha" required>
                    <label for="password_confirmation">Confirmar senha</label>
                </div>

                <button type="submit" class="btn btn-primary fw-bold py-3 w-100" style="background-color:#2557a7;border-color:#2557a7;">
                    Redefinir senha
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

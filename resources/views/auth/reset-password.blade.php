@extends('templates.app')

@section('title', __('site.auth.redefinir'))
@section('description', __('site.auth.redefinir_descricao'))

@section('content')
<div class="auth-wrapper d-flex align-items-center justify-content-center py-5" style="min-height: 80vh; background-color: #f3f2f1;">
    <div class="card shadow-sm border-0" style="width: 100%; max-width: 500px; border-radius: 12px;">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <img src="{{ asset('assets/img/logo.svg') }}" alt="Angola Emprego" height="40" class="mb-3">
                <h4 class="fw-bold text-dark">{{ __('site.auth.nova_senha_titulo') }}</h4>
            </div>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-floating mb-3">
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $email) }}" placeholder="{{ __('site.auth.email') }}" required>
                    <label for="email">{{ __('site.auth.email') }}</label>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-floating mb-3">
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="{{ __('site.auth.nova_senha') }}" required>
                    <label for="password">{{ __('site.auth.nova_senha') }}</label>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="{{ __('site.auth.confirmar_nova_senha') }}" required>
                    <label for="password_confirmation">{{ __('site.auth.confirmar_nova_senha') }}</label>
                </div>

                <button type="submit" class="btn btn-primary fw-bold py-3 w-100" style="background-color:#2557a7;border-color:#2557a7;">
                    {{ __('site.auth.redefinir') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

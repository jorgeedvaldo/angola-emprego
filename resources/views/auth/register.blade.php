@extends('templates.app')

@section('title', 'Registar')
@section('description', 'Crie a sua conta Angola Emprego')

@section('content')
<div class="auth-wrapper d-flex align-items-center justify-content-center py-5" style="min-height: 80vh; background-color: #f3f2f1;">
    <div class="card shadow-sm border-0" style="width: 100%; max-width: 600px; border-radius: 12px;">
        <div class="card-body p-5">
            <div class="text-center mb-5">
                <img src="{{ asset('assets/img/logo.svg') }}" alt="Angola Emprego" height="40" class="mb-3">
                <h4 class="fw-bold text-dark">Criar Conta</h4>
                <p class="text-muted small">Junte-se à nossa comunidade de profissionais.</p>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="form-floating mb-3">
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="Nome Completo" value="{{ old('name') }}" required autofocus style="border-radius: 8px;">
                    <label for="name">Nome Completo</label>
                    @error('name')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <select id="sex" class="form-select @error('sex') is-invalid @enderror" name="sex" required style="border-radius: 8px;">
                                <option value="" disabled selected>Selecione...</option>
                                <option value="M" {{ old('sex') == 'M' ? 'selected' : '' }}>Masculino</option>
                                <option value="F" {{ old('sex') == 'F' ? 'selected' : '' }}>Feminino</option>
                            </select>
                            <label for="sex">Sexo</label>
                        </div>
                        @error('sex')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="date" class="form-control @error('birth_date') is-invalid @enderror" id="birth_date" name="birth_date" value="{{ old('birth_date') }}" required style="border-radius: 8px;">
                            <label for="birth_date">Nascimento</label>
                        </div>
                        @error('birth_date')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="form-floating mb-3">
                    <input type="tel" class="form-control @error('mobile') is-invalid @enderror" id="mobile" name="mobile" placeholder="Telefone" value="{{ old('mobile') }}" required style="border-radius: 8px;">
                    <label for="mobile">Telefone</label>
                     @error('mobile')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-floating mb-3">
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Email" value="{{ old('email') }}" required style="border-radius: 8px;">
                    <label for="email">Email</label>
                     @error('email')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="row g-2 mb-4">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Senha" required style="border-radius: 8px;">
                            <label for="password">Senha</label>
                        </div>
                        @error('password')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="password" class="form-control" id="password-confirm" name="password_confirmation" placeholder="Confirmar" required style="border-radius: 8px;">
                            <label for="password-confirm">Confirmar Senha</label>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 mb-4">
                    <button type="submit" class="btn btn-primary fw-bold py-3" style="border-radius: 8px; background-color: #2557a7; border-color: #2557a7;">
                        Registar
                    </button>
                </div>

                <div class="text-center">
                    <p class="small text-muted mb-0">Já tem uma conta? <a href="{{ route('login') }}" class="text-decoration-none fw-bold" style="color: #2557a7;">Entrar</a></p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@extends('templates.app')

@section('title', 'Registar Empresa')
@section('description', 'Crie a página da sua empresa no Angola Emprego e publique vagas de emprego.')

@section('content')
<div class="auth-wrapper d-flex align-items-center justify-content-center py-5" style="min-height: 80vh; background-color: #f3f2f1;">
    <div class="card shadow-sm border-0" style="width: 100%; max-width: 640px; border-radius: 12px;">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <img src="{{ asset('assets/img/logo.svg') }}" alt="Angola Emprego" height="40" class="mb-3">
                <h4 class="fw-bold text-dark">Registar Empresa</h4>
                <p class="text-muted small mb-0">Crie a página da empresa e publique as suas vagas.</p>
            </div>

            <form method="POST" action="{{ route('register.company') }}">
                @csrf

                <div class="form-floating mb-3">
                    <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" placeholder="Nome da empresa" value="{{ old('company_name') }}" required autofocus style="border-radius: 8px;">
                    <label for="company_name">Nome da empresa</label>
                    @error('company_name')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label small fw-bold text-muted mb-1">URL da página</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">{{ url('/company') }}/</span>
                        <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" placeholder="minha-empresa" value="{{ old('slug') }}" style="border-radius: 0 8px 8px 0;">
                    </div>
                    <small class="text-muted">Deixe em branco para gerar automaticamente. Ex.: <code>minha-empresa</code></small>
                    @error('slug')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-floating mb-3">
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="Nome do responsável" value="{{ old('name') }}" required style="border-radius: 8px;">
                    <label for="name">Nome do responsável</label>
                    @error('name')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="tel" class="form-control @error('mobile') is-invalid @enderror" id="mobile" name="mobile" placeholder="Telefone" value="{{ old('mobile') }}" required style="border-radius: 8px;">
                            <label for="mobile">Telefone</label>
                        </div>
                        @error('mobile')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control @error('location') is-invalid @enderror" id="location" name="location" placeholder="Localização" value="{{ old('location') }}" style="border-radius: 8px;">
                            <label for="location">Localização</label>
                        </div>
                    </div>
                </div>

                <div class="form-floating mb-3">
                    <input type="url" class="form-control @error('website') is-invalid @enderror" id="website" name="website" placeholder="https://" value="{{ old('website') }}" style="border-radius: 8px;">
                    <label for="website">Website (opcional)</label>
                    @error('website')
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
                        Criar página da empresa
                    </button>
                </div>

                <div class="text-center">
                    <p class="small text-muted mb-1">É candidato? <a href="{{ route('register') }}" class="text-decoration-none fw-bold" style="color: #2557a7;">Criar conta pessoal</a></p>
                    <p class="small text-muted mb-0">Já tem uma conta? <a href="{{ route('login') }}" class="text-decoration-none fw-bold" style="color: #2557a7;">Entrar</a></p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

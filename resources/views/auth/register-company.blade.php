@extends('templates.app')

@section('title', __('site.auth.registar_empresa'))
@section('description', __('site.auth.registar_empresa_descricao'))

@section('content')
<div class="auth-wrapper d-flex align-items-center justify-content-center py-5" style="min-height: 80vh; background-color: #f3f2f1;">
    <div class="card shadow-sm border-0" style="width: 100%; max-width: 640px; border-radius: 12px;">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <img src="{{ asset('assets/img/logo.svg') }}" alt="Angola Emprego" height="40" class="mb-3">
                <h4 class="fw-bold text-dark">{{ __('site.auth.registar_empresa') }}</h4>
                <p class="text-muted small mb-0">{{ __('site.auth.registar_empresa_subtitulo') }}</p>
            </div>

            <form method="POST" action="{{ route('register.company') }}" enctype="multipart/form-data">
                @csrf

                <div class="form-floating mb-3">
                    <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" placeholder="{{ __('site.auth.nome_empresa') }}" value="{{ old('company_name') }}" required autofocus style="border-radius: 8px;">
                    <label for="company_name">{{ __('site.auth.nome_empresa') }}</label>
                    @error('company_name')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label small fw-bold text-muted mb-1">{{ __('site.auth.url_pagina') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">{{ url('/company') }}/</span>
                        <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" placeholder="minha-empresa" value="{{ old('slug') }}" style="border-radius: 0 8px 8px 0;">
                    </div>
                    <small class="text-muted">{!! __('site.auth.url_pagina_ajuda') !!}</small>
                    @error('slug')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-floating mb-3">
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="{{ __('site.auth.nome_responsavel') }}" value="{{ old('name') }}" required style="border-radius: 8px;">
                    <label for="name">{{ __('site.auth.nome_responsavel') }}</label>
                    @error('name')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="tel" class="form-control @error('mobile') is-invalid @enderror" id="mobile" name="mobile" placeholder="{{ __('site.auth.telefone') }}" value="{{ old('mobile') }}" required style="border-radius: 8px;">
                            <label for="mobile">{{ __('site.auth.telefone') }}</label>
                        </div>
                        @error('mobile')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control @error('location') is-invalid @enderror" id="location" name="location" placeholder="{{ __('site.auth.localizacao') }}" value="{{ old('location') }}" style="border-radius: 8px;">
                            <label for="location">{{ __('site.auth.localizacao') }}</label>
                        </div>
                    </div>
                </div>

                <div class="form-floating mb-3">
                    <input type="url" class="form-control @error('website') is-invalid @enderror" id="website" name="website" placeholder="https://" value="{{ old('website') }}" style="border-radius: 8px;">
                    <label for="website">{{ __('site.auth.website') }}</label>
                    @error('website')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="card bg-light border mb-3">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">{{ __('site.auth.identidade_visual') }}</h6>

                        <div class="mb-3">
                            <label for="theme_color" class="form-label fw-semibold">{{ __('site.auth.cor_tema') }}</label>
                            <div class="d-flex align-items-center gap-3">
                                <input type="color" class="form-control form-control-color @error('theme_color') is-invalid @enderror" id="theme_color" name="theme_color" value="{{ old('theme_color', '#2557A7') }}" title="{{ __('site.auth.escolher_cor') }}">
                                <span class="small text-muted">{{ __('site.auth.cor_tema_ajuda') }}</span>
                            </div>
                            @error('theme_color')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="logo" class="form-label fw-semibold">{{ __('site.auth.logotipo') }}</label>
                                <input type="file" class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo" accept="image/jpeg,image/png,image/webp">
                                <small class="text-muted">{{ __('site.auth.logotipo_ajuda') }}</small>
                                @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="cover_image" class="form-label fw-semibold">{{ __('site.auth.capa') }}</label>
                                <input type="file" class="form-control @error('cover_image') is-invalid @enderror" id="cover_image" name="cover_image" accept="image/jpeg,image/png,image/webp">
                                <small class="text-muted">{{ __('site.auth.capa_ajuda') }}</small>
                                @error('cover_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-floating mb-3">
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="{{ __('site.auth.email') }}" value="{{ old('email') }}" required style="border-radius: 8px;">
                    <label for="email">{{ __('site.auth.email') }}</label>
                    @error('email')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="row g-2 mb-4">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="{{ __('site.auth.senha') }}" required style="border-radius: 8px;">
                            <label for="password">{{ __('site.auth.senha') }}</label>
                        </div>
                        @error('password')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="password" class="form-control" id="password-confirm" name="password_confirmation" placeholder="{{ __('site.auth.confirmar') }}" required style="border-radius: 8px;">
                            <label for="password-confirm">{{ __('site.auth.confirmar_senha') }}</label>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 mb-4">
                    <button type="submit" class="btn btn-primary fw-bold py-3" style="border-radius: 8px; background-color: #2557a7; border-color: #2557a7;">
                        {{ __('site.auth.criar_pagina_empresa') }}
                    </button>
                </div>

                <div class="text-center">
                    <p class="small text-muted mb-1">{{ __('site.auth.e_candidato') }} <a href="{{ route('register') }}" class="text-decoration-none fw-bold" style="color: #2557a7;">{{ __('site.auth.criar_conta_pessoal_ligacao') }}</a></p>
                    <p class="small text-muted mb-1">{{ __('site.auth.ja_tem_conta') }} <a href="{{ route('login') }}" class="text-decoration-none fw-bold" style="color: #2557a7;">{{ __('site.auth.entrar') }}</a></p>
                    <p class="small text-muted mb-0">{{ __('site.auth.esqueceu_senha') }} <a href="{{ route('password.request') }}" class="text-decoration-none fw-bold" style="color: #2557a7;">{{ __('site.auth.recuperar_acesso') }}</a></p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

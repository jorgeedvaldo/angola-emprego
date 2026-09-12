@extends('templates.app')
@section('title', __('site.painel.titulo'))
@section('description', __('site.painel.descricao'))

@section('content')
<div class="bg-light py-4">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h1 class="fw-bold mb-1">{{ __('site.painel.titulo') }}</h1>
                @if($company->isPublic())
                    <p class="text-muted mb-0">{{ __('site.painel.pagina_publica') }} <a href="{{ url('/company/' . $company->slug) }}">{{ url('/company/' . $company->slug) }}</a></p>
                @else
                    <p class="text-muted mb-0">{{ __('site.painel.ainda_nao_publica') }}</p>
                @endif
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('cv-analyzer.index') }}" class="btn btn-outline-primary fw-bold">
                    <i class="bi bi-stars me-1"></i> {{ __('site.nav.analisar_cvs') }}
                </a>
                @if($company->isPublic())
                    <a href="{{ route('company.jobs.create') }}" class="btn btn-primary fw-bold" style="background-color: #2557a7; border-color: #2557a7;">
                        <i class="bi bi-plus-lg me-1"></i> {{ __('site.painel.publicar_vaga') }}
                    </a>
                @else
                    <button class="btn btn-secondary fw-bold" disabled title="{{ __('site.painel.publicar_bloqueado') }}">
                        <i class="bi bi-lock me-1"></i> {{ __('site.painel.publicar_vaga') }}
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<section class="py-5">
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if(!auth()->user()->hasVerifiedEmail())
            <div class="alert alert-warning d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <strong>{{ __('site.painel.confirme_email') }}</strong>
                    {{ __('site.painel.confirme_email_texto', ['email' => auth()->user()->email]) }}
                </div>
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-dark text-nowrap">{{ __('site.painel.reenviar_email') }}</button>
                </form>
            </div>
        @endif

        @if($company->approval_status === 'pending')
            <div class="alert alert-info">
                <strong>{{ __('site.painel.em_analise_titulo') }}</strong> {{ __('site.painel.em_analise_texto') }}
            </div>
        @elseif($company->approval_status === 'rejected')
            <div class="alert alert-danger">
                <strong>{{ __('site.painel.rejeitado_titulo') }}</strong>
                {{ $company->approval_notes ?: __('site.painel.rejeitado_texto') }}
            </div>
        @endif

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body">
                        <div class="text-muted small">{{ __('site.painel.vagas_publicadas') }}</div>
                        <div class="fs-3 fw-bold">{{ $jobs->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body">
                        <div class="text-muted small">{{ __('site.painel.candidaturas_recebidas') }}</div>
                        <div class="fs-3 fw-bold">{{ $applicationsCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body">
                        <div class="text-muted small">{{ __('site.painel.estado_cadastro') }}</div>
                        <div class="fw-bold">
                            @if($company->approval_status === 'approved')
                                <span class="text-success">{{ __('site.painel.aprovado') }}</span>
                            @elseif($company->approval_status === 'rejected')
                                <span class="text-danger">{{ __('site.painel.nao_aprovado') }}</span>
                            @else
                                <span class="text-warning">{{ __('site.painel.em_analise') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">{{ __('site.painel.pagina_empresa') }}</h5>
                        <form method="POST" action="{{ route('company.update') }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ __('site.painel.nome') }}</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $company->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ __('site.painel.url') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text">/company/</span>
                                    <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $company->slug) }}" required>
                                </div>
                                @error('slug')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ __('site.painel.headline') }}</label>
                                <input type="text" name="headline" class="form-control @error('headline') is-invalid @enderror" maxlength="180" value="{{ old('headline', $company->headline) }}" placeholder="{{ __('site.painel.headline_exemplo') }}">
                                <small class="text-muted">{{ __('site.painel.headline_ajuda') }}</small>
                                @error('headline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ __('site.painel.sobre') }}</label>
                                <textarea name="description" rows="5" class="form-control">{{ old('description', $company->description) }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ __('site.painel.localizacao') }}</label>
                                <input type="text" name="location" class="form-control" value="{{ old('location', $company->location) }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ __('site.painel.website') }}</label>
                                <input type="url" name="website" class="form-control" value="{{ old('website', $company->website) }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ __('site.painel.email_contacto') }}</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $company->email) }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ __('site.painel.telefone') }}</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $company->phone) }}">
                            </div>

                            <div class="border-top pt-3 mt-4 mb-3">
                                <h6 class="fw-bold mb-1">{{ __('site.painel.redes_sociais') }}</h6>
                                <p class="small text-muted mb-3">{{ __('site.painel.redes_sociais_ajuda') }}</p>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold"><i class="bi bi-linkedin me-1"></i> LinkedIn</label>
                                    <input type="url" name="linkedin_url" class="form-control @error('linkedin_url') is-invalid @enderror" value="{{ old('linkedin_url', $company->linkedin_url) }}" placeholder="https://www.linkedin.com/company/...">
                                    @error('linkedin_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold"><i class="bi bi-facebook me-1"></i> Facebook</label>
                                    <input type="url" name="facebook_url" class="form-control @error('facebook_url') is-invalid @enderror" value="{{ old('facebook_url', $company->facebook_url) }}" placeholder="https://www.facebook.com/...">
                                    @error('facebook_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold"><i class="bi bi-instagram me-1"></i> Instagram</label>
                                    <input type="url" name="instagram_url" class="form-control @error('instagram_url') is-invalid @enderror" value="{{ old('instagram_url', $company->instagram_url) }}" placeholder="https://www.instagram.com/...">
                                    @error('instagram_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ __('site.painel.anexos_por_candidatura') }}</label>
                                <input type="number" name="max_attachments" class="form-control @error('max_attachments') is-invalid @enderror" min="1" max="{{ \App\Models\Company::MAX_ATTACHMENTS_LIMIT }}" value="{{ old('max_attachments', $company->max_attachments ?? 1) }}" required>
                                <small class="text-muted">{{ __('site.painel.anexos_ajuda', ['max' => \App\Models\Company::MAX_ATTACHMENTS_LIMIT]) }}</small>
                                @error('max_attachments')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div class="border-top pt-3 mt-4">
                                <h6 class="fw-bold mb-3">{{ __('site.painel.identidade_visual') }}</h6>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ __('site.painel.cor_tema') }}</label>
                                    <div class="d-flex align-items-center gap-3">
                                        <input type="color" name="theme_color" class="form-control form-control-color @error('theme_color') is-invalid @enderror" value="{{ old('theme_color', $company->theme_color) }}" title="{{ __('site.painel.escolher_cor') }}">
                                        <span class="small text-muted">{{ $company->theme_color }}</span>
                                    </div>
                                    @error('theme_color')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ __('site.painel.logotipo') }}</label>
                                @if($company->logo)
                                    <div class="mb-2"><img src="{{ $company->logo_url }}" alt="" style="max-height: 64px;"></div>
                                @endif
                                <input type="file" name="logo" class="form-control" accept="image/*">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ __('site.painel.capa') }}</label>
                                @if($company->cover_image)
                                    <div class="mb-2">
                                        <img src="{{ $company->cover_image_url }}" alt="{{ __('site.painel.capa_actual') }}" class="img-fluid rounded border" style="max-height: 120px; width: 100%; object-fit: cover;">
                                    </div>
                                @endif
                                <input type="file" name="cover_image" class="form-control @error('cover_image') is-invalid @enderror" accept="image/jpeg,image/png,image/webp">
                                <small class="text-muted">{{ __('site.painel.capa_ajuda') }}</small>
                                @error('cover_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <button type="submit" class="btn btn-primary fw-bold w-100" style="background-color: #2557a7; border-color: #2557a7;">{{ __('site.painel.guardar_pagina') }}</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">{{ __('site.painel.as_suas_vagas') }}</h5>
                        @forelse($jobs as $job)
                            <div class="border rounded-3 p-3 mb-3">
                                <div class="d-flex justify-content-between gap-3">
                                    <div>
                                        <a href="{{ url('/vagas/' . $job->slug) }}" class="fw-bold text-dark text-decoration-none">{{ $job->title }}</a>
                                        <div class="small text-muted">{{ $job->location }} · {{ trans_choice('site.painel.candidaturas_contagem', $job->applications_count, ['count' => $job->applications_count]) }}</div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-1 align-items-start">
                                        <a href="{{ route('company.jobs.applications', $job) }}" class="btn btn-sm btn-outline-primary">{{ __('site.painel.candidaturas') }}</a>
                                        <a href="{{ route('company.jobs.edit', $job) }}" class="btn btn-sm btn-outline-secondary">{{ __('site.painel.editar') }}</a>
                                        <form method="POST" action="{{ route('company.jobs.destroy', $job) }}" onsubmit="return confirm('{{ __('site.painel.confirmar_apagar') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">{{ __('site.painel.apagar') }}</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">{{ __('site.painel.sem_vagas') }} <a href="{{ route('company.jobs.create') }}">{{ __('site.painel.publicar_primeira') }}</a>.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

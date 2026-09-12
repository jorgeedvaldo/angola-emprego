@extends('templates.app')
@section('title', __('site.recrutadores.titulo'))
@section('description', __('site.recrutadores.intro'))
@section('canonical_link', url('/empresas-e-recrutadores'))

@section('content')
<div class="bg-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="fw-bold mb-2 text-dark">{{ __('site.recrutadores.titulo') }}</h1>
                <p class="text-muted mb-0" style="max-width: 640px;">{{ __('site.recrutadores.intro') }}</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <a href="{{ route('cv-analysis.info') }}" class="text-decoration-none fw-semibold">
                    <i class="bi bi-question-circle me-1"></i> {{ __('site.recrutadores.como_funciona') }}
                </a>
            </div>
        </div>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="rounded d-flex align-items-center justify-content-center text-white mb-3"
                            style="width: 48px; height: 48px; background-color: #2557a7;">
                            <i class="bi bi-building-add fs-4"></i>
                        </div>
                        <h5 class="fw-bold">{{ __('site.recrutadores.criar_empresa') }}</h5>
                        <p class="text-muted small flex-grow-1">{{ __('site.recrutadores.criar_empresa_texto') }}</p>
                        @auth
                            @if(auth()->user()->isCompany())
                                <a href="{{ route('company.dashboard') }}" class="btn btn-primary fw-bold" style="background-color: #2557a7; border-color: #2557a7;">
                                    <i class="bi bi-speedometer2 me-1"></i> {{ __('site.recrutadores.ir_para_painel') }}
                                </a>
                            @else
                                <a href="{{ route('companies.index') }}" class="btn btn-outline-primary fw-bold">
                                    <i class="bi bi-building me-1"></i> {{ __('site.recrutadores.ver_empresas') }}
                                </a>
                            @endif
                        @else
                            <a href="{{ route('register.company') }}" class="btn btn-primary fw-bold" style="background-color: #2557a7; border-color: #2557a7;">
                                <i class="bi bi-plus-lg me-1"></i> {{ __('site.recrutadores.registar_empresa') }}
                            </a>
                        @endauth
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="rounded d-flex align-items-center justify-content-center text-white mb-3"
                            style="width: 48px; height: 48px; background-color: #2557a7;">
                            <i class="bi bi-buildings fs-4"></i>
                        </div>
                        <h5 class="fw-bold">{{ __('site.recrutadores.lista_empresas') }}</h5>
                        <p class="text-muted small flex-grow-1">{{ __('site.recrutadores.lista_empresas_texto', ['count' => $companiesCount]) }}</p>
                        <a href="{{ route('companies.index') }}" class="btn btn-outline-primary fw-bold">
                            <i class="bi bi-list-ul me-1"></i> {{ __('site.recrutadores.ver_lista') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="rounded d-flex align-items-center justify-content-center text-white mb-3"
                            style="width: 48px; height: 48px; background-color: #2557a7;">
                            <i class="bi bi-stars fs-4"></i>
                        </div>
                        <h5 class="fw-bold">{{ __('site.recrutadores.analisar_cv') }}</h5>
                        <p class="text-muted small flex-grow-1">{{ __('site.recrutadores.analisar_cv_texto') }}</p>
                        <a href="{{ route('cv-analyzer.index') }}" class="btn btn-primary fw-bold" style="background-color: #2557a7; border-color: #2557a7;">
                            <i class="bi bi-stars me-1"></i> {{ __('site.recrutadores.abrir_analisador') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if($latestCompanies->isNotEmpty())
            <div class="d-flex justify-content-between align-items-center mt-5 mb-3">
                <h5 class="fw-bold mb-0">{{ __('site.recrutadores.empresas_recentes') }}</h5>
                <a href="{{ route('companies.index') }}" class="small text-decoration-none">{{ __('site.recrutadores.ver_todas') }}</a>
            </div>
            <div class="row g-3">
                @foreach($latestCompanies as $company)
                    <div class="col-6 col-md-4 col-lg-2">
                        <a href="{{ url('/company/' . $company->slug) }}" class="text-decoration-none">
                            <div class="card border-0 shadow-sm h-100 text-center" style="border-radius: 12px;">
                                <div class="card-body p-3">
                                    @if($company->logo)
                                        <img src="{{ $company->logo_url }}" alt="{{ $company->name }}" class="rounded border mb-2"
                                            style="width: 48px; height: 48px; object-fit: contain;">
                                    @else
                                        <div class="rounded d-inline-flex align-items-center justify-content-center text-white fw-bold mb-2"
                                            style="width: 48px; height: 48px; background-color: #2557a7;">
                                            {{ strtoupper(substr($company->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="small fw-semibold text-dark text-truncate">{{ $company->name }}</div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection

@extends('templates.app')
@section('title', __('site.empresas.meta_titulo'))
@section('description', __('site.empresas.meta_descricao'))
@section('canonical_link', url('/empresas'))

@section('content')
<div class="bg-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="fw-bold mb-2 text-dark">{{ __('site.empresas.titulo') }}</h1>
                <p class="text-muted mb-0">{{ __('site.empresas.subtitulo') }}</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <a href="{{ route('register.company') }}" class="btn btn-primary fw-bold rounded-pill px-4" style="background-color: #2557a7; border-color: #2557a7;">
                    <i class="bi bi-building-add me-1"></i> {{ __('site.empresas.registar') }}
                </a>
            </div>
        </div>
    </div>
</div>

<section class="section py-5">
    <div class="container">
        @if($companies->isEmpty())
            <div class="text-center py-5 bg-white rounded-3 shadow-sm border">
                <i class="bi bi-building display-4 text-muted"></i>
                <p class="mt-3 text-muted mb-4">{{ __('site.empresas.sem_empresas') }}</p>
                <a href="{{ route('register.company') }}" class="btn btn-primary">{{ __('site.empresas.seja_a_primeira') }}</a>
            </div>
        @else
            <div class="row g-4">
                @foreach($companies as $company)
                    <div class="col-md-6 col-lg-4">
                        <a href="{{ url('/company/' . $company->slug) }}" class="text-decoration-none">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        @if($company->logo)
                                            <img src="{{ $company->logo_url }}" alt="{{ $company->name }}" class="rounded border" style="width: 56px; height: 56px; object-fit: contain;">
                                        @else
                                            <div class="rounded d-flex align-items-center justify-content-center text-white fw-bold" style="width: 56px; height: 56px; background-color: #2557a7;">
                                                {{ strtoupper(substr($company->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <h5 class="fw-bold text-dark mb-0">{{ $company->name }}</h5>
                                            <small class="text-muted">{{ $company->location ?: 'Angola' }}</small>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-2">{{ \Illuminate\Support\Str::limit(strip_tags($company->description), 110) }}</p>
                                    <span class="badge bg-light text-dark border">{{ trans_choice('site.empresas.contagem_vagas', $company->jobs_count, ['count' => $company->jobs_count]) }}</span>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $companies->links() }}</div>
        @endif
    </div>
</section>
@endsection

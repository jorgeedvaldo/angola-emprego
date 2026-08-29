@extends('templates.app')
@section('title', $company->name)
@section('description', Str::limit(strip_tags($company->description ?: $company->name . ' — vagas de emprego no Angola Emprego'), 160))
@section('canonical_link', url('/company/' . $company->slug))
@if($company->logo)
@section('og_image', $company->logo_url)
@endif

@section('content')
<div class="bg-light py-5">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Início</a></li>
                <li class="breadcrumb-item"><a href="{{ route('companies.index') }}">Empresas</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $company->name }}</li>
            </ol>
        </nav>

        <div class="bg-white p-4 p-lg-5 rounded-3 shadow-sm border">
            <div class="row align-items-center">
                <div class="col-lg-2 mb-3 mb-lg-0 text-center">
                    @if($company->logo)
                        <img src="{{ $company->logo_url }}" alt="{{ $company->name }}" class="img-fluid rounded shadow-sm" style="max-height: 100px; object-fit: contain;">
                    @else
                        <div class="rounded d-flex align-items-center justify-content-center text-white fw-bold mx-auto" style="width: 88px; height: 88px; background-color: #2557a7; font-size: 2rem;">
                            {{ strtoupper(substr($company->name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div class="col-lg-10">
                    <h1 class="fw-bold text-dark mb-2">{{ $company->name }}</h1>
                    <div class="d-flex flex-wrap gap-3 text-muted mb-2">
                        @if($company->location)
                            <span><i class="bi bi-geo-alt me-1"></i> {{ $company->location }}</span>
                        @endif
                        @if($company->website)
                            <span><i class="bi bi-globe me-1"></i> <a href="{{ $company->website }}" target="_blank" rel="noopener">{{ $company->website }}</a></span>
                        @endif
                    </div>
                    <p class="text-muted small mb-0">{{ url('/company/' . $company->slug) }}</p>
                </div>
            </div>
            @if($company->description)
                <div class="mt-4 pt-3 border-top">
                    {!! nl2br(e($company->description)) !!}
                </div>
            @endif
        </div>
    </div>
</div>

<section class="section py-5">
    <div class="container">
        <h2 class="fw-bold mb-4">Vagas abertas</h2>
        @forelse($jobs as $job)
            <a href="{{ url('/vagas/' . $job->slug) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
                    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-start p-4">
                        <div>
                            <h5 class="fw-bold text-dark mb-1">{{ $job->title }}</h5>
                            <div class="text-muted small">
                                <i class="bi bi-geo-alt me-1"></i> {{ $job->location }}
                                <span class="ms-3"><i class="bi bi-calendar3 me-1"></i> {{ $job->created_at->format('d/m/Y') }}</span>
                            </div>
                        </div>
                        <span class="btn btn-outline-primary btn-sm fw-bold rounded-pill mt-3 mt-md-0">Ver vaga</span>
                    </div>
                </div>
            </a>
        @empty
            <div class="bg-white p-5 text-center rounded-3 shadow-sm border text-muted">
                Esta empresa ainda não publicou vagas.
            </div>
        @endforelse

        <div class="mt-3">{{ $jobs->links() }}</div>
    </div>
</section>
@endsection

@extends('templates.app')

@section('title', 'Perfil de ' . $user->name)
@section('description', 'Perfil público de ' . $user->name . ' - Veja as áreas de interesse e os currículos disponíveis.')

@section('content')
<div class="bg-light py-5">
    <div class="container text-center">
        <div class="mb-4 d-flex justify-content-center">
            @if($user->avatar)
                <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="rounded-circle shadow" style="width: 120px; height: 120px; object-fit: cover; border: 4px solid white;">
            @else
                <div class="avatar-placeholder rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center shadow fs-1 fw-bold" style="width: 120px; height: 120px; border: 4px solid white;">
                    {{ substr($user->name, 0, 1) }}
                </div>
            @endif
        </div>
        <h1 class="fw-bold mb-2 text-dark">{{ $user->name }}</h1>
        <p class="text-muted d-inline-flex align-items-center bg-white px-3 py-1 rounded-pill shadow-sm">
            <i class="bi bi-person-badge fs-5 me-2 text-primary"></i> Perfil Profissional
        </p>
    </div>
</div>

<section class="section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <!-- Categories Section -->
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h4 class="card-title fw-bold mb-0 text-primary">
                            <i class="bi bi-briefcase me-2"></i> Disponível para as seguintes áreas
                        </h4>
                    </div>
                    <div class="card-body p-4 pt-0">
                        @if($user->categories->count() > 0)
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                @foreach($user->categories as $category)
                                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fs-6" style="font-weight: 500;">
                                        {{ $category->name }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-secondary mb-0 bg-opacity-50 border-0 text-muted">
                                <i class="bi bi-info-circle me-2"></i> O utilizador ainda não definiu as suas áreas de interesse.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- CVs Section -->
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h4 class="card-title fw-bold mb-0 text-primary">
                            <i class="bi bi-file-earmark-pdf me-2"></i> Currículos
                        </h4>
                    </div>
                    <div class="card-body p-4 pt-0">
                        @if($user->cvs->count() > 0)
                            <div class="row g-3 mt-1">
                                @foreach($user->cvs as $cv)
                                    <div class="col-md-6">
                                        <div class="card h-100 border rounded-3 hover-shadow transition-all">
                                            <div class="card-body p-3 d-flex align-items-center">
                                                <div class="bg-danger bg-opacity-10 p-3 rounded-circle me-3">
                                                    <i class="bi bi-filetype-pdf text-danger fs-3"></i>
                                                </div>
                                                <div class="flex-grow-1 overflow-hidden">
                                                    <h6 class="mb-1 text-truncate fw-bold text-dark" title="{{ $cv->name }}">{{ $cv->name }}</h6>
                                                    <div class="d-flex align-items-center mb-0">
                                                        <small class="text-muted d-block me-2">{{ $cv->created_at->format('d/m/Y') }}</small>
                                                        @if($cv->is_primary)
                                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2" style="font-size: 0.65rem;">
                                                                <i class="bi bi-star-fill me-1"></i> Principal
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-footer bg-transparent border-top p-0">
                                                <a href="{{ asset('storage/' . $cv->path) }}" target="_blank" class="btn btn-light w-100 py-2 fw-medium text-primary rounded-bottom border-0">
                                                    <i class="bi bi-eye me-2"></i> Visualizar CV
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-secondary mb-0 bg-opacity-50 border-0 text-muted">
                                <i class="bi bi-info-circle me-2"></i> Não há currículos disponíveis no momento.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Call to action / Footer element -->
                <div class="text-center mt-5">
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="bi bi-house me-2"></i> Voltar ao Início
                    </a>
                </div>

            </div>
        </div>
    </div>
</section>

<style>
    .hover-shadow:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        transform: translateY(-2px);
    }
    .transition-all {
        transition: all 0.3s ease;
    }
</style>
@endsection

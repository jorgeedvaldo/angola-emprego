@extends('templates.app')

@section('title', 'Vagas Sugeridas para Si')
@section('description', 'Encontre as melhores oportunidades baseadas no seu perfil e interesses.')

@section('content')
<div class="bg-light py-5">
    <div class="container">
         <h1 class="fw-bold mb-2 text-dark">Vagas Sugeridas</h1>
         <p class="text-muted mb-0">Baseado nas categorias que selecionou no seu perfil.</p>
    </div>
</div>

<section class="section py-5">
    <div class="container">
        
        @if($jobs->count() > 0)
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                @foreach($jobs as $job)
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm hover-shadow transition-all" style="border-radius: 12px;">
                        <a href="{{ url('/vaga/' . $job->slug) }}" class="text-decoration-none text-dark">
                            <div class="position-relative">
                                <img src="{{ $job->image_url ?? asset('assets/img/job-placeholder.jpg') }}" class="card-img-top object-fit-cover" alt="{{ $job->title }}" style="height: 180px; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                                <span class="position-absolute top-0 end-0 m-3 badge bg-light text-dark shadow-sm">
                                    {{ $job->type ?? 'Tempo Inteiro' }}
                                </span>
                            </div>
                            <div class="card-body p-4">
                                <div class="mb-2">
                                    @foreach($job->categories->take(2) as $category)
                                        <span class="badge bg-primary bg-opacity-10 text-primary small">{{ $category->name }}</span>
                                    @endforeach
                                </div>
                                <h5 class="card-title fw-bold mb-2 text-truncate">{{ $job->title }}</h5>
                                <p class="card-text text-muted small mb-3">
                                    <i class="bi bi-building me-1"></i> {{ $job->company ?? 'Empresa Confidencial' }}
                                </p>
                                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                    <small class="text-muted"><i class="bi bi-geo-alt me-1"></i> {{ $job->location ?? 'Angola' }}</small>
                                    <small class="text-secondary fw-medium">{{ $job->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-5 d-flex justify-content-center">
                {{ $jobs->links() }}
            </div>

        @else
            <div class="text-center py-5">
                <i class="bi bi-briefcase display-1 text-muted opacity-25"></i>
                <h3 class="mt-4 text-dark fw-bold">Nenhuma sugestão encontrada</h3>
                <p class="text-muted">Ainda não encontrámos vagas para as suas categorias, ou ainda não selecionou nenhuma categoria.</p>
                <a href="{{ route('profile.show') }}" class="btn btn-primary rounded-pill mt-3 px-4">
                    <i class="bi bi-pencil-square me-2"></i> Editar Preferências
                </a>
            </div>
        @endif

    </div>
</section>

<style>
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,.15)!important;
    }
    .transition-all {
        transition: all 0.3s ease;
    }
    .object-fit-cover {
        object-fit: cover;
    }
</style>
@endsection

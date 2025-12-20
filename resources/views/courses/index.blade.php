@extends('templates.app')

@section('title', 'Cursos')
@section('description', 'Aprenda com nossos cursos online')

@section('content')
<div class="bg-light py-5">
    <div class="container">

        <div class="text-center mb-5">
            <h2 class="fw-bold text-dark">Desenvolva as suas competências</h2>
            <p class="text-muted lead">Cursos práticos para impulsionar a sua carreira</p>
        </div>

        <div class="row g-4">
            @forelse($courses as $course)
            <div class="col-lg-4 col-md-6 d-flex align-items-stretch">
                <a href="{{ route('courses.show', $course->slug) }}" class="text-decoration-none w-100">
                    <div class="card h-100 border-0 shadow-hover transition-all" style="border-radius: 12px; overflow: hidden;">
                        <div class="position-relative">
                            <img src="{{ asset('storage/' . $course->image) }}" class="card-img-top" alt="{{ $course->title }}" style="height: 220px; object-fit: cover;">
                            @if($course->lessons->count() > 0)
                                <span class="position-absolute bottom-0 end-0 bg-dark text-white px-3 py-1 m-3 rounded-pill small bg-opacity-75">
                                    <i class="bi bi-play-circle me-1"></i> {{ $course->lessons->count() }} Aulas
                                </span>
                            @endif
                        </div>
                        <div class="card-body p-4 d-flex flex-column">
                            <h5 class="card-title fw-bold text-dark mb-2">{{ $course->title }}</h5>
                             <p class="card-text text-muted small flex-grow-1 description-truncate">
                                {!! \Illuminate\Support\Str::limit(strip_tags($course->description), 120) !!}
                            </p>
                            <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                                <span class="text-primary fw-bold small">Começar agora</span>
                                <i class="bi bi-arrow-right text-primary"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <div class="p-5 bg-white rounded-3 shadow-sm">
                    <i class="bi bi-journal-x text-muted display-4 mb-3"></i>
                    <h4 class="text-muted">Nenhum curso disponível no momento.</h4>
                    <p class="text-muted">Volte mais tarde para novas oportunidades de aprendizado.</p>
                </div>
            </div>
            @endforelse
        </div>

    </div>
</div>

<style>
.shadow-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 1rem 3rem rgba(0,0,0,.1)!important;
}
.transition-all {
    transition: all 0.3s ease;
}
</style>
@endsection

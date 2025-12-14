@extends('templates.app')

@section('title', $lesson->title . ' - ' . $course->title)

@section('content')
<div class="bg-dark py-4 text-white">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('courses.index') }}" class="text-white-50 text-decoration-none">Cursos</a></li>
                <li class="breadcrumb-item"><a href="{{ route('courses.show', $course->slug) }}" class="text-white-50 text-decoration-none">{{ $course->title }}</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">{{ $lesson->title }}</li>
            </ol>
        </nav>
    </div>
</div>

<section class="section bg-light py-5">
    <div class="container" data-aos="fade-up">

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="bg-black rounded shadow overflow-hidden mb-4">
                    <div class="ratio ratio-16x9">
                        @if(str_contains($lesson->video_url, 'youtube.com') || str_contains($lesson->video_url, 'youtu.be'))
                            @php
                                // Extract ID (Simple regex for standard YT links)
                                preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $lesson->video_url, $matches);
                                $videoId = $matches[1] ?? null;
                            @endphp
                            @if($videoId)
                                <iframe src="https://www.youtube.com/embed/{{ $videoId }}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            @else
                                <div class="d-flex justify-content-center align-items-center text-white h-100">Vídeo inválido</div>
                            @endif
                        @else
                            <iframe src="{{ $lesson->video_url }}" title="Video player" allowfullscreen></iframe>
                        @endif
                    </div>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 bg-white p-4 rounded shadow-sm border">
                    <div class="mb-3 mb-md-0">
                        <h2 class="fw-bold m-0 text-dark">{{ $lesson->title }}</h2>
                    </div>
                   <form action="{{ route('courses.complete', ['slug' => $course->slug, 'lessonSlug' => $lesson->slug]) }}" method="POST">
                       @csrf
                       <button type="submit" class="btn btn-success btn-lg px-4"><i class="bi bi-check-circle-fill me-2"></i> Marcar como Concluída</button>
                   </form>
                </div>

                <div class="d-flex justify-content-between">
                    @if($previous)
                        <a href="{{ route('courses.attend', ['slug' => $course->slug, 'lessonSlug' => $previous->slug]) }}" class="btn btn-outline-secondary px-4 py-2"><i class="bi bi-arrow-left me-2"></i> Aula Anterior</a>
                    @else
                        <div></div>
                    @endif

                    @if($next)
                        <a href="{{ route('courses.attend', ['slug' => $course->slug, 'lessonSlug' => $next->slug]) }}" class="btn btn-outline-primary px-4 py-2">Próxima Aula <i class="bi bi-arrow-right ms-2"></i></a>
                    @else
                         <a href="{{ route('courses.show', $course->slug) }}" class="btn btn-primary px-4 py-2">Voltar ao Curso</a>
                    @endif
                </div>

            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header bg-white py-3 border-bottom">
                         <h5 class="fw-bold m-0"><i class="bi bi-list-ul me-2"></i> Conteúdo do Curso</h5>
                    </div>
                    <div class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                        @foreach($course->lessons as $l)
                            @php
                                 $isActive = $l->id === $lesson->id;
                                 $isCompleted = auth()->user()->lessons()->where('lesson_id', $l->id)->whereNotNull('completed_at')->exists();
                            @endphp
                            <a href="{{ route('courses.attend', ['slug' => $course->slug, 'lessonSlug' => $l->slug]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 {{ $isActive ? 'bg-light border-start border-4 border-primary' : '' }}">
                                <div class="me-2">
                                    <div class="{{ $isActive ? 'fw-bold text-primary' : 'text-dark' }}">{{ $loop->iteration }}. {{ $l->title }}</div>
                                    <small class="text-muted">{{ $l->duration_minutes }} min</small>
                                </div>
                                @if($isCompleted)
                                    <span class="text-success fs-5"><i class="bi bi-check-circle-fill"></i></span>
                                @elseif($isActive)
                                    <span class="text-primary fs-5"><i class="bi bi-play-circle-fill"></i></span>
                                @else
                                    <span class="text-muted fs-5"><i class="bi bi-play-circle"></i></span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection

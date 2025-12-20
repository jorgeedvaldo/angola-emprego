@extends('templates.app')

@section('title', $course->title)
@section('description', \Illuminate\Support\Str::limit(strip_tags($course->description), 150))

@section('content')
<div class="bg-light py-5">
    <div class="container">

        <div class="row g-5">
             <div class="col-lg-8">
                <div class="bg-white p-4 rounded-3 shadow-sm mb-4">
                     <img src="{{ asset('storage/' . $course->image) }}" alt="{{ $course->title }}" class="img-fluid rounded mb-4 w-100" style="max-height: 400px; object-fit: cover;">
                     <h2 class="fw-bold mb-3 text-dark">{{ $course->title }}</h2>
                     <div class="content text-muted mb-4 lead" style="font-size: 1rem; line-height: 1.6;">
                        {!! $course->description !!}
                     </div>
                </div>

                <div class="bg-white p-4 rounded-3 shadow-sm">
                    <h3 class="fw-bold mb-4 text-dark border-bottom pb-2">Conteúdo do Curso</h3>
                    <div class="list-group list-group-flush">
                        @forelse($course->lessons as $lesson)
                            @php
                                $isCompleted = auth()->check() && auth()->user()->lessons()->where('lesson_id', $lesson->id)->whereNotNull('completed_at')->exists();
                            @endphp
                            <a href="{{ route('courses.attend', ['slug' => $course->slug, 'lessonSlug' => $lesson->slug]) }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-play-circle-fill text-primary me-3 fs-4"></i>
                                    <div>
                                        <h6 class="mb-0 fw-bold {{ $isCompleted ? 'text-decoration-line-through text-muted' : 'text-dark' }}">{{ $lesson->title }}</h6>
                                        <small class="text-muted">{{ $lesson->duration_minutes }} minutos</small>
                                    </div>
                                </div>
                                @if($isCompleted)
                                    <span class="badge bg-success rounded-pill px-3"><i class="bi bi-check-lg"></i></span>
                                @else
                                    <span class="badge bg-light text-dark rounded-pill px-3 border">Assistir</span>
                                @endif
                            </a>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-camera-video-off fs-3 mb-2"></i>
                                <p>Nenhuma aula disponível ainda.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 sticky-top" style="top: 100px; border-radius: 12px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4 text-dark">O seu Progresso</h5>
                        @auth
                            @php
                                $completedCount = auth()->user()->lessons()->whereIn('lesson_id', $course->lessons->pluck('id'))->whereNotNull('completed_at')->count();
                                $totalLessons = $course->lessons->count();
                                $percent = $totalLessons > 0 ? ($completedCount / $totalLessons) * 100 : 0;
                            @endphp
                            <div class="d-flex justify-content-between mb-2">
                                <span class="small fw-bold text-muted">{{ $completedCount }} de {{ $totalLessons }} aulas</span>
                                <span class="small fw-bold text-primary">{{ round($percent) }}%</span>
                            </div>
                            <div class="progress mb-4" style="height: 10px; border-radius: 5px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            
                            @if($percent == 100)
                                <div class="alert alert-success d-flex align-items-center mb-3" role="alert">
                                  <i class="bi bi-trophy-fill me-2 fs-5"></i>
                                  <div>Parabéns! Curso concluído.</div>
                                </div>
                                <div class="d-grid">
                                    <a href="{{ route('courses.certificate', $course->slug) }}" class="btn btn-warning text-white fw-bold py-2"><i class="bi bi-award-fill me-2"></i> Baixar Certificado</a>
                                </div>
                            @else
                                <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i> Conclua todas as aulas para desbloquear o seu certificado.</p>
                            @endif
                        @else
                            <div class="text-center py-3">
                                <p class="text-muted mb-4">Faça login para acompanhar seu progresso e obter o certificado.</p>
                                <div class="d-grid">
                                    <a href="{{ route('login') }}" class="btn btn-primary fw-bold">Entrar agora</a>
                                </div>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

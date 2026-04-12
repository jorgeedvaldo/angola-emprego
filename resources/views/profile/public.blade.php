@extends('templates.app')

@section('title', 'Perfil de ' . $user->name)
@section('description', ($user->bio ? \Illuminate\Support\Str::limit($user->bio, 160) : 'Perfil profissional de ' . $user->name . ' — Habilidades, experiência e formação.'))

@section('content')
{{-- Hero Header --}}
<div class="profile-hero">
    <div class="profile-hero-bg"></div>
    <div class="container position-relative">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <div class="profile-avatar-wrapper mb-3">
                    @if($user->avatar)
                        <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="profile-avatar">
                    @else
                        <div class="profile-avatar profile-avatar-placeholder">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <h1 class="fw-bold mb-1 text-dark">{{ $user->name }}</h1>
                <p class="text-muted mb-2" style="font-size: 0.95rem;">
                    <i class="bi bi-at"></i>{{ $user->username }}
                </p>

                @if($user->bio)
                    <p class="profile-bio mx-auto">{{ $user->bio }}</p>
                @endif
                
                {{-- Social Share Buttons --}}
                <div class="d-flex justify-content-center gap-2 mt-3">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('profile.public', $user->username)) }}" target="_blank" class="btn share-btn share-facebook" title="Partilhar no Facebook">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(route('profile.public', $user->username)) }}&title={{ urlencode('Perfil Profissional de ' . $user->name) }}" target="_blank" class="btn share-btn share-linkedin" title="Partilhar no LinkedIn">
                        <i class="bi bi-linkedin"></i>
                    </a>
                    <a href="https://api.whatsapp.com/send?text={{ urlencode('Veja o meu perfil profissional: ' . route('profile.public', $user->username)) }}" target="_blank" class="btn share-btn share-whatsapp" title="Partilhar no WhatsApp">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center g-4">
            
            {{-- Main Column --}}
            <div class="col-lg-8">

                {{-- Completed Courses --}}
                @if($completedCourses->count() > 0)
                <div class="profile-card mb-4">
                    <div class="profile-card-header">
                        <h4 class="profile-card-title">
                            <i class="bi bi-mortarboard-fill text-primary me-2"></i> Cursos Concluídos
                        </h4>
                    </div>
                    <div class="profile-card-body">
                        <div class="row g-3">
                            @foreach($completedCourses as $course)
                                <div class="col-md-6">
                                    <div class="course-item">
                                        <div class="d-flex align-items-start">
                                            <div class="course-icon me-3">
                                                <i class="bi bi-journal-check"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="fw-bold mb-1">{{ $course->title }}</h6>
                                                <a href="{{ route('certificates.verify', ['user' => $user->id, 'course' => $course->slug]) }}" class="btn btn-sm btn-outline-primary rounded-pill mt-1" target="_blank">
                                                    <i class="bi bi-award me-1"></i> Ver Certificado
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- Skills --}}
                @if($user->skills->count() > 0)
                <div class="profile-card mb-4">
                    <div class="profile-card-header">
                        <h4 class="profile-card-title">
                            <i class="bi bi-lightning-fill text-warning me-2"></i> Habilidades
                        </h4>
                    </div>
                    <div class="profile-card-body">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($user->skills as $skill)
                                <span class="skill-badge">{{ $skill->name }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- Work Experience --}}
                @if($user->experiences->count() > 0)
                <div class="profile-card mb-4">
                    <div class="profile-card-header">
                        <h4 class="profile-card-title">
                            <i class="bi bi-briefcase-fill text-success me-2"></i> Experiência Profissional
                        </h4>
                    </div>
                    <div class="profile-card-body">
                        <div class="timeline">
                            @foreach($user->experiences as $exp)
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-content">
                                        <h6 class="fw-bold mb-0">{{ $exp->position }}</h6>
                                        <p class="text-primary mb-1 fw-medium">{{ $exp->company }}</p>
                                        <small class="text-muted d-block mb-2">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            {{ $exp->start_date ? $exp->start_date->format('m/Y') : '—' }}
                                            —
                                            {{ $exp->end_date ? $exp->end_date->format('m/Y') : 'Presente' }}
                                        </small>
                                        @if($exp->description)
                                            <p class="text-muted small mb-0">{{ $exp->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- Education --}}
                @if($user->educations->count() > 0)
                <div class="profile-card mb-4">
                    <div class="profile-card-header">
                        <h4 class="profile-card-title">
                            <i class="bi bi-book-fill text-info me-2"></i> Formação Académica
                        </h4>
                    </div>
                    <div class="profile-card-body">
                        <div class="timeline">
                            @foreach($user->educations as $edu)
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-content">
                                        <h6 class="fw-bold mb-0">{{ $edu->institution }}</h6>
                                        @if($edu->degree || $edu->field_of_study)
                                            <p class="text-primary mb-1 fw-medium">
                                                {{ $edu->degree }}{{ $edu->degree && $edu->field_of_study ? ' — ' : '' }}{{ $edu->field_of_study }}
                                            </p>
                                        @endif
                                        @if($edu->start_year || $edu->end_year)
                                            <small class="text-muted">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                {{ $edu->start_year ?? '—' }} — {{ $edu->end_year ?? 'Presente' }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- Languages --}}
                @if($user->languages->count() > 0)
                <div class="profile-card mb-4">
                    <div class="profile-card-header">
                        <h4 class="profile-card-title">
                            <i class="bi bi-translate text-danger me-2"></i> Idiomas
                        </h4>
                    </div>
                    <div class="profile-card-body">
                        <div class="row g-3">
                            @foreach($user->languages as $lang)
                                <div class="col-sm-6 col-md-4">
                                    <div class="language-item">
                                        <span class="fw-bold">{{ $lang->language }}</span>
                                        <span class="language-level level-{{ \Illuminate\Support\Str::slug($lang->level) }}">{{ $lang->level }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- Categories --}}
                @if($user->categories->count() > 0)
                <div class="profile-card mb-4">
                    <div class="profile-card-header">
                        <h4 class="profile-card-title">
                            <i class="bi bi-tags-fill text-secondary me-2"></i> Áreas de Interesse
                        </h4>
                    </div>
                    <div class="profile-card-body">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($user->categories as $category)
                                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fs-6" style="font-weight: 500;">
                                    {{ $category->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- CVs --}}
                @if($user->cvs->count() > 0)
                <div class="profile-card mb-4">
                    <div class="profile-card-header">
                        <h4 class="profile-card-title">
                            <i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i> Currículos
                        </h4>
                    </div>
                    <div class="profile-card-body">
                        <div class="row g-3">
                            @foreach($user->cvs as $cv)
                                <div class="col-md-6">
                                    <div class="cv-item">
                                        <div class="d-flex align-items-center">
                                            <div class="cv-icon me-3">
                                                <i class="bi bi-filetype-pdf"></i>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <h6 class="mb-1 text-truncate fw-bold" title="{{ $cv->name }}">{{ $cv->name }}</h6>
                                                <div class="d-flex align-items-center">
                                                    <small class="text-muted me-2">{{ $cv->created_at->format('d/m/Y') }}</small>
                                                    @if($cv->is_primary)
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2" style="font-size: 0.65rem;">
                                                            <i class="bi bi-star-fill me-1"></i> Principal
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <a href="{{ asset('storage/' . $cv->path) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill ms-2">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Sidebar: QR Code --}}
            <div class="col-lg-4">
                <div class="profile-card sticky-lg-top" style="top: 90px;">
                    <div class="profile-card-header">
                        <h4 class="profile-card-title">
                            <i class="bi bi-qr-code me-2"></i> Código QR
                        </h4>
                    </div>
                    <div class="profile-card-body text-center">
                        <div id="qrcode" class="d-inline-block mb-3"></div>
                        <p class="text-muted small mb-0">Digitalize para aceder a este perfil</p>
                    </div>
                </div>

                {{-- CTA --}}
                <div class="text-center mt-4">
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="bi bi-house me-2"></i> Voltar ao Início
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>

<style>
    /* Profile Hero */
    .profile-hero {
        position: relative;
        padding: 50px 0 40px;
        background: linear-gradient(135deg, #f0f4ff 0%, #e8f0fe 50%, #f5f0ff 100%);
        overflow: hidden;
    }
    .profile-hero-bg {
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(37, 87, 167, 0.06), rgba(99, 102, 241, 0.06));
        pointer-events: none;
    }
    .profile-avatar-wrapper {
        display: inline-block;
    }
    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid white;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    .profile-avatar-placeholder {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #2557a7, #6366f1);
        color: #fff;
        font-size: 2.5rem;
        font-weight: 700;
    }
    .profile-bio {
        max-width: 540px;
        color: #555;
        font-size: 0.95rem;
        line-height: 1.6;
    }

    /* Share buttons */
    .share-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .share-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        color: #fff;
    }
    .share-facebook { background-color: #3b5998; }
    .share-linkedin { background-color: #0077b5; }
    .share-whatsapp { background-color: #25D366; }

    /* Cards */
    .profile-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e8e8e8;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        transition: box-shadow 0.3s ease;
    }
    .profile-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    }
    .profile-card-header {
        padding: 20px 24px 0;
    }
    .profile-card-title {
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 0;
        color: #1a1a2e;
    }
    .profile-card-body {
        padding: 16px 24px 24px;
    }

    /* Skills */
    .skill-badge {
        background: linear-gradient(135deg, #eef2ff, #e8f0fe);
        color: #2557a7;
        padding: 6px 16px;
        border-radius: 50px;
        font-size: 0.875rem;
        font-weight: 600;
        border: 1px solid rgba(37, 87, 167, 0.12);
        transition: all 0.2s ease;
    }
    .skill-badge:hover {
        background: linear-gradient(135deg, #2557a7, #6366f1);
        color: #fff;
        transform: translateY(-1px);
    }

    /* Timeline */
    .timeline {
        position: relative;
        padding-left: 24px;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 6px;
        top: 8px;
        bottom: 8px;
        width: 2px;
        background: #e0e0e0;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 24px;
    }
    .timeline-item:last-child {
        padding-bottom: 0;
    }
    .timeline-dot {
        position: absolute;
        left: -21px;
        top: 6px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #2557a7;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #2557a7;
    }

    /* Language items */
    .language-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 14px;
        background: #fafbfc;
        border-radius: 8px;
        border: 1px solid #eee;
    }
    .language-level {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 50px;
        background: #e8e8e8;
        color: #555;
    }
    .language-level.level-nativo { background: #dcfce7; color: #166534; }
    .language-level.level-fluente { background: #dbeafe; color: #1e40af; }
    .language-level.level-avancado { background: #e0e7ff; color: #3730a3; }
    .language-level.level-intermediario { background: #fef3c7; color: #92400e; }
    .language-level.level-basico { background: #f3f4f6; color: #6b7280; }

    /* Course item */
    .course-item {
        padding: 14px;
        background: #fafbfc;
        border-radius: 10px;
        border: 1px solid #eee;
        transition: all 0.2s ease;
    }
    .course-item:hover {
        border-color: #c7d2fe;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.08);
    }
    .course-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg, #eef2ff, #e0e7ff);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #4f46e5;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    /* CV item */
    .cv-item {
        padding: 14px;
        background: #fafbfc;
        border-radius: 10px;
        border: 1px solid #eee;
        transition: all 0.2s ease;
    }
    .cv-item:hover {
        border-color: #fca5a5;
        box-shadow: 0 2px 8px rgba(220, 38, 38, 0.06);
    }
    .cv-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #dc2626;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    /* QR Code container */
    #qrcode {
        padding: 12px;
        background: #fff;
        border-radius: 12px;
        border: 1px solid #eee;
    }
    #qrcode img, #qrcode canvas {
        border-radius: 8px;
    }
</style>
@endsection

@section('footer-scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var qrcodeContainer = document.getElementById('qrcode');
        if (qrcodeContainer) {
            new QRCode(qrcodeContainer, {
                text: "{{ route('profile.public', $user->username) }}",
                width: 180,
                height: 180,
                colorDark: "#1a1a2e",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        }
    });
</script>
@endsection

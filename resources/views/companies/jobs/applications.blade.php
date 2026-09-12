@extends('templates.app')
@section('title', __('site.candidaturas.meta_titulo', ['vaga' => $job->title]))
@section('description', __('site.candidaturas.descricao'))

@section('content')
<div class="bg-light py-4">
    <div class="container">
        <a href="{{ route('company.dashboard') }}" class="small text-decoration-none">&larr; {{ __('site.candidaturas.voltar') }}</a>
        <h1 class="fw-bold mt-2">{{ __('site.candidaturas.titulo') }}</h1>
        <p class="text-muted mb-0">{{ $job->title }}</p>
    </div>
</div>

<section class="py-4">
    <div class="container">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="fw-bold mb-1">{{ __('site.candidaturas.analise_titulo') }}</h5>
                        <p class="text-muted small mb-0">{{ __('site.candidaturas.analise_texto') }}</p>
                    </div>
                    <button id="cv-analysis-start" type="button" class="btn btn-primary fw-bold text-nowrap" style="background-color: #2557a7; border-color: #2557a7;">
                        <i class="bi bi-stars me-1"></i> {{ __('site.candidaturas.analisar') }}
                    </button>
                </div>
                <div id="cv-analysis-progress-wrap" class="mt-3 d-none">
                    <div class="progress" style="height: 8px;">
                        <div id="cv-analysis-progress-bar" class="progress-bar" role="progressbar" style="width: 0%; background-color: #2557a7;"></div>
                    </div>
                    <div id="cv-analysis-status" class="small text-muted mt-2">{{ __('site.candidaturas.a_preparar') }}</div>
                </div>
            </div>
        </div>

        @forelse($applications as $application)
            <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                        <div>
                            <h5 class="fw-bold mb-0">
                                {{ $application->name }}
                                @if($application->match_score !== null)
                                    <span class="badge {{ $application->match_score >= 0.5 ? 'bg-success' : ($application->match_score >= 0.3 ? 'bg-warning text-dark' : 'bg-secondary') }} ms-2">
                                        {{ __('site.candidaturas.compativel', ['percent' => round(max(0, $application->match_score) * 100)]) }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border ms-2">{{ __('site.candidaturas.nao_analisado') }}</span>
                                @endif
                            </h5>
                            <div class="small text-muted">{{ $application->email }} @if($application->phone)· {{ $application->phone }}@endif</div>
                            @if(!empty($application->matched_keywords))
                                <div class="small text-muted mt-1">
                                    <i class="bi bi-check2-circle text-success"></i>
                                    {{ __('site.candidaturas.termos_encontrados') }} {{ implode(', ', array_slice($application->matched_keywords, 0, 12)) }}
                                </div>
                            @endif
                        </div>
                        <div class="text-muted small">{{ $application->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <p class="fw-semibold mb-1">{{ __('site.candidaturas.assunto') }} {{ $application->subject }}</p>
                    <p class="mb-3">{{ $application->message }}</p>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($application->attachmentList() as $file)
                            @php
                                $downloadUrl = $file->id
                                    ? route('company.attachments.download', $file)
                                    : route('company.applications.download', $application);
                                $isPdf = Str::endsWith(strtolower($file->original_name ?: ''), '.pdf');
                            @endphp
                            @if($isPdf)
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#cv-viewer-modal"
                                    data-cv-preview-url="{{ $downloadUrl }}" data-cv-preview-name="{{ $application->name }} — {{ $file->original_name ?: 'CV' }}">
                                    <i class="bi bi-eye me-1"></i> {{ __('site.candidaturas.ver_cv') }}
                                </button>
                            @endif
                            <a href="{{ $downloadUrl }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download me-1"></i> {{ $file->original_name ?: __('site.candidaturas.anexo') }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white p-5 text-center rounded-3 shadow-sm border text-muted">
                {{ __('site.candidaturas.sem_candidaturas') }}
            </div>
        @endforelse
    </div>
</section>

@if($applications->isNotEmpty())
    @php
        $applicationsForAnalysis = $applications->filter(function ($application) {
            return $application->attachmentList()->isNotEmpty();
        })->map(function ($application) {
            return [
                'id' => $application->id,
                'name' => $application->name,
                'hasVector' => (bool) $application->has_current_vector,
                'analyzeUrl' => route('company.applications.analyze', $application),
            ];
        })->values();
    @endphp
    <script>
        window.CV_ANALYSIS_CONFIG = {
            // O texto vem daqui já no idioma escolhido: o JavaScript não lê os
            // ficheiros de tradução.
            strings: @json(__('site.candidaturas')),
            job: {
                hasVector: {{ $jobHasCurrentVector ? 'true' : 'false' }},
                analyzeUrl: '{{ route('company.jobs.analyze', $job) }}',
            },
            applications: @json($applicationsForAnalysis),
        };
    </script>
    <script src="{{ asset('assets/js/cv-analysis.js') }}"></script>

    <div class="modal fade" id="cv-viewer-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cv-viewer-title">CV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('site.candidaturas.fechar') }}"></button>
                </div>
                <div class="modal-body bg-light" id="cv-viewer-body"></div>
            </div>
        </div>
    </div>
    <script>
        window.CV_VIEWER_CONFIG = {
            strings: @json(__('site.candidaturas')),
            pdfJsUrl: '{{ asset('assets/vendor/pdfjs/pdf.min.js') }}',
            pdfWorkerUrl: '{{ asset('assets/vendor/pdfjs/pdf.worker.min.js') }}',
        };
    </script>
    <script src="{{ asset('assets/js/cv-viewer.js') }}"></script>
@endif
@endsection

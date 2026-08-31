@extends('templates.app')
@section('title', 'Candidaturas — ' . $job->title)
@section('description', 'Candidaturas recebidas para a vaga')

@section('content')
<div class="bg-light py-4">
    <div class="container">
        <a href="{{ route('company.dashboard') }}" class="small text-decoration-none">&larr; Voltar ao painel</a>
        <h1 class="fw-bold mt-2">Candidaturas</h1>
        <p class="text-muted mb-0">{{ $job->title }}</p>
    </div>
</div>

<section class="py-4">
    <div class="container">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="fw-bold mb-1">Análise de CVs por IA</h5>
                        <p class="text-muted small mb-0">
                            Ordena automaticamente os candidatos pela compatibilidade com a descrição da vaga.
                            Tudo é processado no seu navegador — os CVs não saem do seu computador durante a análise.
                        </p>
                    </div>
                    <button id="cv-analysis-start" type="button" class="btn btn-primary fw-bold text-nowrap" style="background-color: #2557a7; border-color: #2557a7;">
                        <i class="bi bi-stars me-1"></i> Analisar candidaturas
                    </button>
                </div>
                <div id="cv-analysis-progress-wrap" class="mt-3 d-none">
                    <div class="progress" style="height: 8px;">
                        <div id="cv-analysis-progress-bar" class="progress-bar" role="progressbar" style="width: 0%; background-color: #2557a7;"></div>
                    </div>
                    <div id="cv-analysis-status" class="small text-muted mt-2">A preparar…</div>
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
                                        {{ round(max(0, $application->match_score) * 100) }}% compatível
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border ms-2">Ainda não analisado</span>
                                @endif
                            </h5>
                            <div class="small text-muted">{{ $application->email }} @if($application->phone)· {{ $application->phone }}@endif</div>
                        </div>
                        <div class="text-muted small">{{ $application->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <p class="fw-semibold mb-1">Assunto: {{ $application->subject }}</p>
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
                                    <i class="bi bi-eye me-1"></i> Ver CV
                                </button>
                            @endif
                            <a href="{{ $downloadUrl }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download me-1"></i> {{ $file->original_name ?: 'Anexo' }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white p-5 text-center rounded-3 shadow-sm border text-muted">
                Ainda não há candidaturas para esta vaga.
            </div>
        @endforelse
    </div>
</section>

@if($applications->isNotEmpty())
    @php
        $applicationsForAnalysis = $applications->filter(function ($application) {
            return $application->attachmentList()->isNotEmpty();
        })->map(function ($application) {
            $file = $application->attachmentList()->first();

            return [
                'id' => $application->id,
                'name' => $application->name,
                'hasVector' => (bool) $application->has_current_vector,
                'downloadUrl' => $file->id
                    ? route('company.attachments.download', $file)
                    : route('company.applications.download', $application),
                'vectorUrl' => route('company.applications.vector', $application),
            ];
        })->values();
    @endphp
    <script>
        window.CV_ANALYSIS_CONFIG = {
            transformersUrl: '{{ asset('assets/vendor/transformers/transformers.min.js') }}',
            pdfJsUrl: '{{ asset('assets/vendor/pdfjs/pdf.min.js') }}',
            pdfWorkerUrl: '{{ asset('assets/vendor/pdfjs/pdf.worker.min.js') }}',
            tesseractUrl: '{{ asset('assets/vendor/tesseract/tesseract.min.js') }}',
            job: {
                hasVector: {{ $jobHasCurrentVector ? 'true' : 'false' }},
                descriptionText: @json($jobDescriptionText),
                vectorUrl: '{{ route('company.jobs.vector', $job) }}',
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body bg-light" id="cv-viewer-body"></div>
            </div>
        </div>
    </div>
    <script>
        window.CV_VIEWER_CONFIG = {
            pdfJsUrl: '{{ asset('assets/vendor/pdfjs/pdf.min.js') }}',
            pdfWorkerUrl: '{{ asset('assets/vendor/pdfjs/pdf.worker.min.js') }}',
        };
    </script>
    <script src="{{ asset('assets/js/cv-viewer.js') }}"></script>
@endif
@endsection

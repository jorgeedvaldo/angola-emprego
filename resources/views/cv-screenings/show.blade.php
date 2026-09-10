@extends('templates.app')
@section('title', 'Triagem — ' . $screening->title)
@section('description', 'CVs ordenados por compatibilidade com a descrição da vaga')

@section('content')
@php
    $analyzedCount = $candidates->where('has_current_vector', true)->count();
    $pendingCount = $candidates->count() - $analyzedCount;
    $remaining = $screening->remainingSlots();
@endphp

<div class="bg-light py-4">
    <div class="container">
        <a href="{{ route('cv-screenings.index') }}" class="small text-decoration-none">&larr; Voltar ao analisador</a>
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mt-2">
            <div>
                <h1 class="fw-bold mb-1">{{ $screening->title }}</h1>
                <p class="text-muted mb-0">
                    {{ $candidates->count() }} CV(s) · {{ $analyzedCount }} analisado(s) ·
                    criada a {{ $screening->created_at->format('d/m/Y H:i') }}
                </p>
            </div>
            <form method="POST" action="{{ route('cv-screenings.destroy', $screening) }}"
                onsubmit="return confirm('Remover esta triagem e todos os CVs carregados?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-trash me-1"></i> Remover triagem
                </button>
            </form>
        </div>
    </div>
</div>

<section class="py-4">
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="fw-bold mb-1">Análise de CVs por IA</h5>
                        <p class="text-muted small mb-0">
                            @if($pendingCount > 0)
                                {{ $pendingCount }} CV(s) por analisar. A análise lê o texto de cada CV e ordena-os
                                pela compatibilidade com a descrição desta vaga.
                            @else
                                Todos os CVs desta triagem já foram analisados e estão ordenados abaixo.
                            @endif
                        </p>
                    </div>
                    @if($candidates->isNotEmpty())
                        <button id="cv-analysis-start" type="button" class="btn btn-primary fw-bold text-nowrap"
                            style="background-color: #2557a7; border-color: #2557a7;" @disabled($pendingCount === 0 && $screeningHasCurrentVector)>
                            <i class="bi bi-stars me-1"></i> Analisar CVs
                        </button>
                    @endif
                </div>
                <div id="cv-analysis-progress-wrap" class="mt-3 d-none">
                    <div class="progress" style="height: 8px;">
                        <div id="cv-analysis-progress-bar" class="progress-bar" role="progressbar" style="width: 0%; background-color: #2557a7;"></div>
                    </div>
                    <div id="cv-analysis-status" class="small text-muted mt-2">A preparar…</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8 order-2 order-lg-1">
                @forelse($candidates as $index => $candidate)
                    <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
                        <div class="card-body p-4">
                            <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                                <div>
                                    <h5 class="fw-bold mb-0">
                                        <span class="text-muted me-1">{{ $index + 1 }}.</span>
                                        {{ $candidate->label() }}
                                        @if($candidate->match_score !== null)
                                            <span class="badge {{ $candidate->match_score >= 0.5 ? 'bg-success' : ($candidate->match_score >= 0.3 ? 'bg-warning text-dark' : 'bg-secondary') }} ms-2">
                                                {{ round(max(0, $candidate->match_score) * 100) }}% compatível
                                            </span>
                                        @else
                                            <span class="badge bg-light text-muted border ms-2">Ainda não analisado</span>
                                        @endif
                                    </h5>
                                    <div class="small text-muted">{{ $candidate->original_name }}</div>
                                    @if(!empty($candidate->matched_keywords))
                                        <div class="small text-muted mt-1">
                                            <i class="bi bi-check2-circle text-success"></i>
                                            Termos da vaga encontrados no CV:
                                            {{ implode(', ', array_slice($candidate->matched_keywords, 0, 12)) }}
                                        </div>
                                    @endif
                                </div>
                                @if($candidate->cv_analyzed_at)
                                    <div class="text-muted small text-nowrap">{{ $candidate->cv_analyzed_at->format('d/m/Y H:i') }}</div>
                                @endif
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#cv-viewer-modal"
                                    data-cv-preview-url="{{ route('cv-screenings.cvs.download', [$screening, $candidate]) }}"
                                    data-cv-preview-name="{{ $candidate->original_name }}">
                                    <i class="bi bi-eye me-1"></i> Ver CV
                                </button>
                                <a href="{{ route('cv-screenings.cvs.download', [$screening, $candidate]) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download me-1"></i> Descarregar
                                </a>
                                <form method="POST" action="{{ route('cv-screenings.cvs.destroy', [$screening, $candidate]) }}"
                                    onsubmit="return confirm('Remover este CV da triagem?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-x-lg me-1"></i> Remover
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white p-5 text-center rounded-3 shadow-sm border text-muted">
                        Esta triagem ainda não tem CVs. Carregue-os no formulário ao lado.
                    </div>
                @endforelse
            </div>

            <div class="col-lg-4 order-1 order-lg-2">
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">Adicionar mais CVs</h6>
                        @if($remaining > 0)
                            <form method="POST" action="{{ route('cv-screenings.cvs.store', $screening) }}" enctype="multipart/form-data">
                                @csrf
                                <input type="file" name="cvs[]" multiple accept="application/pdf,.pdf" required class="form-control mb-2">
                                <div class="form-text mb-3">
                                    Restam {{ $remaining }} lugar(es) nesta triagem. Só PDF, no máximo 5 MB cada.
                                </div>
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-upload me-1"></i> Carregar CVs
                                </button>
                            </form>
                        @else
                            <p class="text-muted small mb-0">
                                Esta triagem atingiu o limite de {{ \App\Models\CvScreening::MAX_CVS_PER_SCREENING }} CVs.
                            </p>
                        @endif
                    </div>
                </div>

                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">Descrição da vaga</h6>
                        <div class="text-muted small" style="white-space: pre-wrap; max-height: 320px; overflow-y: auto;">{{ $screening->description }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@if($candidates->isNotEmpty())
    @php
        $candidatesForAnalysis = $candidates->map(fn ($candidate) => [
            'id' => $candidate->id,
            'name' => $candidate->label(),
            'hasVector' => (bool) $candidate->has_current_vector,
            'analyzeUrl' => route('cv-screenings.cvs.analyze', [$screening, $candidate]),
        ])->values();
    @endphp
    <script>
        // Mesmo formato de configuração que a análise de candidaturas: o campo "job"
        // representa aqui a descrição escrita pelo recrutador.
        window.CV_ANALYSIS_CONFIG = {
            job: {
                hasVector: {{ $screeningHasCurrentVector ? 'true' : 'false' }},
                analyzeUrl: '{{ route('cv-screenings.analyze', $screening) }}',
            },
            applications: @json($candidatesForAnalysis),
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

@extends('templates.app')
@section('title', __('site.analisador.meta_titulo'))
@section('description', __('site.analisador.meta_descricao'))
@section('canonical_link', url('/analisador-de-cv'))

@section('content')
<div class="bg-light py-4">
    <div class="container">
        <a href="{{ route('recruiters.index') }}" class="small text-decoration-none">&larr; {{ __('site.recrutadores.titulo') }}</a>
        <h1 class="fw-bold mt-2 mb-1">{{ __('site.analisador.titulo') }}</h1>
        <p class="text-muted mb-0" style="max-width: 720px;">{{ __('site.analisador.intro') }}</p>
    </div>
</div>

<section class="py-4 py-md-5">
    <div class="container">
        <div class="alert alert-light border d-flex gap-3 align-items-start">
            <i class="bi bi-shield-check fs-4 text-success"></i>
            <div class="small mb-0">
                <strong>{{ __('site.analisador.privacidade_titulo') }}</strong>
                {{ __('site.analisador.privacidade_texto') }}
            </div>
        </div>

        <div id="cv-analyzer-alert" class="alert alert-warning d-none"></div>

        <div class="row g-4">
            <div class="col-lg-5">
                <form id="cv-analyzer-form" class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label for="cv-analyzer-title" class="form-label fw-semibold">{{ __('site.analisador.nome_vaga') }}</label>
                            <input type="text" id="cv-analyzer-title" maxlength="255" class="form-control"
                                placeholder="{{ __('site.analisador.nome_vaga_exemplo') }}">
                            <div class="form-text">{{ __('site.analisador.nome_vaga_ajuda') }}</div>
                        </div>

                        <div class="mb-3">
                            <label for="cv-analyzer-description" class="form-label fw-semibold">{{ __('site.analisador.descricao') }}</label>
                            <textarea id="cv-analyzer-description" rows="12" class="form-control"
                                placeholder="{{ __('site.analisador.descricao_exemplo') }}"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="cv-analyzer-files" class="form-label fw-semibold">{{ __('site.analisador.ficheiros') }}</label>
                            <input type="file" id="cv-analyzer-files" multiple accept="application/pdf,.pdf" class="form-control">
                            <div class="form-text">{{ __('site.analisador.ficheiros_ajuda', ['max' => \App\Http\Controllers\CvAnalyzerController::MAX_CVS]) }}</div>
                        </div>

                        <button type="submit" id="cv-analyzer-start" class="btn btn-primary fw-bold w-100"
                            style="background-color: #2557a7; border-color: #2557a7;">
                            <i class="bi bi-stars me-1"></i> {{ __('site.analisador.analisar') }}
                        </button>

                        <div id="cv-analyzer-progress-wrap" class="mt-3 d-none">
                            <div class="progress" style="height: 8px;">
                                <div id="cv-analyzer-progress-bar" class="progress-bar" role="progressbar"
                                    style="width: 0%; background-color: #2557a7;"></div>
                            </div>
                            <div id="cv-analyzer-status" class="small text-muted mt-2"></div>
                        </div>
                    </div>
                </form>

                <div class="card border-0 shadow-sm mt-4" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">{{ __('site.analisador.como_funciona') }}</h6>
                        <ol class="text-muted small mb-0 ps-3">
                            <li class="mb-2">{{ __('site.analisador.passo_1') }}</li>
                            <li class="mb-2">{{ __('site.analisador.passo_2') }}</li>
                            <li class="mb-2">{{ __('site.analisador.passo_3') }}</li>
                            <li>{{ __('site.analisador.passo_4') }}</li>
                        </ol>
                        <p class="text-muted small mb-0 mt-3">{!! __('site.analisador.dica_requisitos') !!}</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">{{ __('site.analisador.resultado') }}</h5>
                    <span id="cv-analyzer-count" class="text-muted small"></span>
                </div>

                <div id="cv-analyzer-empty" class="bg-white p-5 text-center rounded-3 shadow-sm border text-muted">
                    {{ __('site.analisador.vazio') }}
                </div>

                <div id="cv-analyzer-requirements" class="alert alert-light border d-none"></div>

                <div id="cv-analyzer-results"></div>
            </div>
        </div>
    </div>
</section>

<script>
    window.CV_ANALYZER_CONFIG = {
        jobUrl: '{{ route('cv-analyzer.job') }}',
        cvUrl: '{{ route('cv-analyzer.cv') }}',
        maxFiles: {{ \App\Http\Controllers\CvAnalyzerController::MAX_CVS }},
        // O JavaScript não tem acesso aos ficheiros de tradução: o texto vem
        // daqui já no idioma que o visitante escolheu.
        strings: @json(__('site.js')),
    };
</script>
<script src="{{ asset('assets/js/cv-analyzer.js') }}"></script>
@endsection

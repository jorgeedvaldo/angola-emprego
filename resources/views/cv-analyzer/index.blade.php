@extends('templates.app')
@section('title', 'Analisador de CVs — compare currículos com a sua vaga')
@section('description', 'Escreva a descrição da vaga, carregue os CVs e receba-os ordenados por compatibilidade. Grátis, sem criar conta e sem guardar os ficheiros.')
@section('canonical_link', url('/analisador-de-cv'))

@section('content')
<div class="bg-light py-4">
    <div class="container">
        <a href="{{ route('recruiters.index') }}" class="small text-decoration-none">&larr; Empresas e Recrutadores</a>
        <h1 class="fw-bold mt-2 mb-1">Analisador de CVs</h1>
        <p class="text-muted mb-0" style="max-width: 720px;">
            Escreva a descrição da vaga, escolha os CVs que tem em mão e receba-os ordenados do candidato mais
            compatível para o menos compatível. Não precisa de conta.
        </p>
    </div>
</div>

<section class="py-4 py-md-5">
    <div class="container">
        <div class="alert alert-light border d-flex gap-3 align-items-start">
            <i class="bi bi-shield-check fs-4 text-success"></i>
            <div class="small mb-0">
                <strong>Os CVs não são guardados.</strong>
                Os ficheiros ficam no seu computador; cada um é enviado uma única vez, só durante a análise, e não é
                arquivado nem no site nem em nenhuma conta. Feche a página e não fica nada.
            </div>
        </div>

        <div id="cv-analyzer-alert" class="alert alert-warning d-none"></div>

        <div class="row g-4">
            <div class="col-lg-5">
                <form id="cv-analyzer-form" class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label for="cv-analyzer-description" class="form-label fw-semibold">Descrição da vaga</label>
                            <textarea id="cv-analyzer-description" rows="12" class="form-control"
                                placeholder="Descreva as funções, os requisitos, a formação e a experiência pretendida. Quanto mais detalhada a descrição, melhor a ordenação."></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="cv-analyzer-files" class="form-label fw-semibold">CVs (PDF)</label>
                            <input type="file" id="cv-analyzer-files" multiple accept="application/pdf,.pdf" class="form-control">
                            <div class="form-text">
                                Até {{ \App\Http\Controllers\CvAnalyzerController::MAX_CVS }} ficheiros, no máximo 5 MB
                                cada. Só PDF — é o formato de que conseguimos ler o texto.
                            </div>
                        </div>

                        <button type="submit" id="cv-analyzer-start" class="btn btn-primary fw-bold w-100"
                            style="background-color: #2557a7; border-color: #2557a7;">
                            <i class="bi bi-stars me-1"></i> Analisar CVs
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
                        <h6 class="fw-bold mb-3">Como funciona</h6>
                        <ol class="text-muted small mb-0 ps-3">
                            <li class="mb-2">Separamos o anúncio nos requisitos que pede, um a um.</li>
                            <li class="mb-2">Lemos o texto de cada CV e dividimo-lo em blocos.</li>
                            <li class="mb-2">Para cada requisito procuramos o bloco do CV que melhor lhe responde,
                                e confirmamos se os termos do requisito aparecem mesmo no texto.</li>
                            <li>Cada CV fica com a lista do que cumpre e do que falta, e a ordenação sai daí.</li>
                        </ol>
                        <p class="text-muted small mb-0 mt-3">
                            Escreva os requisitos em linhas separadas, debaixo de um título como
                            <em>Requisitos</em> — é assim que conseguimos lê-los um a um.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Resultado</h5>
                    <span id="cv-analyzer-count" class="text-muted small"></span>
                </div>

                <div id="cv-analyzer-empty" class="bg-white p-5 text-center rounded-3 shadow-sm border text-muted">
                    Escolha os CVs à esquerda e clique em “Analisar CVs”.
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
    };
</script>
<script src="{{ asset('assets/js/cv-analyzer.js') }}"></script>
@endsection

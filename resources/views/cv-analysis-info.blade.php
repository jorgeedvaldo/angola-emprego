@extends('templates.app')
@section('title', __('site.ia_info.meta_titulo'))
@section('description', __('site.ia_info.meta_descricao'))
@section('canonical_link', url('/analise-de-cv'))

@section('content')
    <!-- Page Header -->
    <div class="bg-light py-5">
        <div class="container text-center">
            <h1 class="fw-bold mb-3 text-dark">{{ __('site.ia_info.meta_titulo') }}</h1>
            <p class="text-muted mx-auto" style="max-width: 640px;">
                {{ __('site.ia_info.intro') }}
            </p>
        </div>
    </div>

    <!-- 4 passos -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <span class="text-primary fw-bold text-uppercase small">{{ __('site.ia_info.passo_a_passo') }}</span>
                <h2 class="fw-bold text-dark mt-2">{{ __('site.ia_info.do_cadastro') }}</h2>
            </div>

            <div class="row gy-4">
                <div class="col-md-6 col-lg-3">
                    <div class="p-4 border rounded-3 h-100 bg-light">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width:36px;height:36px;">1</div>
                            <i class="bi bi-building fs-3 text-primary"></i>
                        </div>
                        <h5 class="fw-bold">{{ __('site.ia_info.p1_titulo') }}</h5>
                        <p class="text-muted small mb-0">
                            {{ __('site.ia_info.p1_texto') }}
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="p-4 border rounded-3 h-100 bg-light">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width:36px;height:36px;">2</div>
                            <i class="bi bi-person-badge fs-3 text-primary"></i>
                        </div>
                        <h5 class="fw-bold">{{ __('site.ia_info.p2_titulo') }}</h5>
                        <p class="text-muted small mb-0">
                            {{ __('site.ia_info.p2_texto') }}
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="p-4 border rounded-3 h-100 bg-light">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width:36px;height:36px;">3</div>
                            <i class="bi bi-briefcase fs-3 text-primary"></i>
                        </div>
                        <h5 class="fw-bold">{{ __('site.ia_info.p3_titulo') }}</h5>
                        <p class="text-muted small mb-0">
                            {{ __('site.ia_info.p3_texto') }}
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="p-4 border rounded-3 h-100 bg-light">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width:36px;height:36px;">4</div>
                            <i class="bi bi-robot fs-3 text-primary"></i>
                        </div>
                        <h5 class="fw-bold">{{ __('site.ia_info.p4_titulo') }}</h5>
                        <p class="text-muted small mb-0">
                            {{ __('site.ia_info.p4_texto') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Explicação simples do passo 4 -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center gy-5">
                <div class="col-lg-6">
                    <span class="text-primary fw-bold text-uppercase small">{{ __('site.ia_info.ia_seccao') }}</span>
                    <h2 class="fw-bold text-dark mt-2 mb-4">{{ __('site.ia_info.porque_util') }}</h2>
                    <p class="text-muted mb-3">
                        {{ __('site.ia_info.porque_util_1') }}
                    </p>
                    <p class="text-muted mb-3">
                        {{ __('site.ia_info.porque_util_2') }}
                    </p>
                    <p class="text-muted mb-0">
                        {{ __('site.ia_info.porque_util_3') }}
                    </p>
                </div>
                <div class="col-lg-6">
                    <div class="p-4 p-lg-5 bg-white rounded-3 shadow-sm border">
                        <h5 class="fw-bold mb-3">{{ __('site.ia_info.como_sabe') }}</h5>
                        <p class="text-muted mb-3">
                            {{ __('site.ia_info.como_sabe_1') }}
                        </p>
                        <p class="text-muted mb-3">
                            {{ __('site.ia_info.como_sabe_2') }}
                        </p>
                        <p class="text-muted mb-0">
                            {{ __('site.ia_info.como_sabe_3') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold text-dark">{{ __('site.ia_info.faq') }}</h2>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion accordion-flush" id="cvAnalysisFaqAccordion">
                        <div class="accordion-item border mb-3 rounded shadow-sm overflow-hidden">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#cvfaq1">
                                    {{ __('site.ia_info.faq1_pergunta') }}
                                </button>
                            </h2>
                            <div id="cvfaq1" class="accordion-collapse collapse" data-bs-parent="#cvAnalysisFaqAccordion">
                                <div class="accordion-body text-muted">{{ __('site.ia_info.faq1_resposta') }}</div>
                            </div>
                        </div>

                        <div class="accordion-item border mb-3 rounded shadow-sm overflow-hidden">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#cvfaq2">
                                    {{ __('site.ia_info.faq2_pergunta') }}
                                </button>
                            </h2>
                            <div id="cvfaq2" class="accordion-collapse collapse" data-bs-parent="#cvAnalysisFaqAccordion">
                                <div class="accordion-body text-muted">{{ __('site.ia_info.faq2_resposta') }}</div>
                            </div>
                        </div>

                        <div class="accordion-item border mb-3 rounded shadow-sm overflow-hidden">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#cvfaq3">
                                    {{ __('site.ia_info.faq3_pergunta') }}
                                </button>
                            </h2>
                            <div id="cvfaq3" class="accordion-collapse collapse" data-bs-parent="#cvAnalysisFaqAccordion">
                                <div class="accordion-body text-muted">{{ __('site.ia_info.faq3_resposta') }}</div>
                            </div>
                        </div>

                        <div class="accordion-item border mb-3 rounded shadow-sm overflow-hidden">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#cvfaq4">
                                    {{ __('site.ia_info.faq4_pergunta') }}
                                </button>
                            </h2>
                            <div id="cvfaq4" class="accordion-collapse collapse" data-bs-parent="#cvAnalysisFaqAccordion">
                                <div class="accordion-body text-muted">{{ __('site.ia_info.faq4_resposta') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA final -->
    <section class="py-5 bg-primary text-white text-center">
        <div class="container">
            <h2 class="fw-bold mb-3">{{ __('site.ia_info.pronto') }}</h2>
            <p class="mb-4 opacity-75">{{ __('site.ia_info.pronto_texto') }}</p>
            <a href="{{ route('register.company') }}" class="btn btn-light btn-lg rounded-pill fw-bold px-4">
                <i class="bi bi-building-add me-2"></i> {{ __('site.ia_info.cadastrar') }}
            </a>
        </div>
    </section>
@endsection

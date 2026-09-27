{{--
    Anúncio da análise de candidatos, no fim das vagas e dos artigos.

    Não aparece a quem já tem conta de empresa: quem já se cadastrou não precisa
    que lhe ofereçam o cadastro, e a secção só lhe roubava espaço à página.
--}}
@unless(auth()->check() && auth()->user()->isCompany())
<section class="promo-empresas py-5">
    <div class="container">
        <div class="promo-empresas-caixa p-4 p-lg-5">
            <div class="row align-items-center gy-4">

                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
                        <span class="badge rounded-pill promo-empresas-etiqueta">{{ __('site.promo_empresas.etiqueta') }}</span>
                        <span class="text-uppercase fw-bold small promo-empresas-sobretitulo">
                            {{ __('site.promo_empresas.sobretitulo') }}
                        </span>
                    </div>

                    <h2 class="fw-bold text-white mb-3">{{ __('site.promo_empresas.titulo') }}</h2>

                    <p class="promo-empresas-texto mb-4">{{ __('site.promo_empresas.texto') }}</p>

                    <div class="row g-3 mb-4">
                        @foreach([
                            ['icone' => 'bi-ticket-perforated', 'titulo' => 'ponto1', 'nota' => 'ponto1_nota'],
                            ['icone' => 'bi-file-earmark-text', 'titulo' => 'ponto2', 'nota' => 'ponto2_nota'],
                            ['icone' => 'bi-bar-chart-steps',   'titulo' => 'ponto3', 'nota' => 'ponto3_nota'],
                        ] as $ponto)
                            <div class="col-sm-4">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="bi {{ $ponto['icone'] }} promo-empresas-icone" aria-hidden="true"></i>
                                    <div>
                                        <div class="fw-bold text-white small">{{ __('site.promo_empresas.' . $ponto['titulo']) }}</div>
                                        <div class="promo-empresas-nota">{{ __('site.promo_empresas.' . $ponto['nota']) }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="promo-empresas-cartao p-4 h-100">
                        <h3 class="h5 fw-bold mb-2">{{ __('site.promo_empresas.criar') }}</h3>
                        <p class="text-muted small mb-4">{{ __('site.promo_empresas.criar_nota') }}</p>

                        <a href="{{ route('register.company') }}" class="btn btn-primary fw-bold w-100 mb-2">
                            <i class="bi bi-building-add me-1"></i> {{ __('site.promo_empresas.criar') }}
                        </a>

                        <a href="{{ route('cv-analysis.info') }}" class="btn btn-outline-secondary w-100">
                            {{ __('site.promo_empresas.como_funciona') }}
                            <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

@once
<style>
    .promo-empresas-caixa {
        border-radius: 1rem;
        background: linear-gradient(135deg, #0b1526 0%, #14294a 55%, #16325c 100%);
        background-color: #0b1526;
    }

    .promo-empresas-etiqueta {
        background-color: #ffc107;
        color: #1f2d3d;
        font-weight: 700;
        letter-spacing: .03em;
    }

    .promo-empresas-sobretitulo { color: #9fc0ef; letter-spacing: .06em; }
    .promo-empresas-texto { color: #cfdcf0; }
    .promo-empresas-nota { color: #9fb3ce; font-size: .8rem; }

    .promo-empresas-icone {
        color: #6fa8ee;
        font-size: 1.25rem;
        line-height: 1.2;
    }

    .promo-empresas-cartao {
        background-color: #fff;
        border-radius: .75rem;
    }
</style>
@endonce
@endunless

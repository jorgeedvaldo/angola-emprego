{{--
    Faixa "Siga-nos no Google News".

    O endereço é a página de preferências de fontes do Google: quem lá chega
    marca o angolaemprego.com como fonte a seguir e passa a receber as nossas
    vagas e notícias no seu Google News.

    Aparece duas vezes na mesma página (no início e no fim das vagas e dos
    artigos), por isso o bloco de estilos está dentro de um @once.
--}}
<div class="gnews py-3">
    <div class="container">
        <a href="https://www.google.com/preferences/source?q=angolaemprego.com"
           target="_blank" rel="noopener"
           class="gnews-cartao"
           aria-label="{{ __('site.google_news.aria') }}">

            <span class="gnews-icone" aria-hidden="true"><i class="bi bi-newspaper"></i></span>

            <span class="gnews-texto">
                <span class="gnews-titulo">
                    {{ __('site.google_news.siga') }}
                    <span class="gnews-marca" aria-hidden="true"><span
                            class="gnews-azul">G</span><span
                            class="gnews-vermelho">o</span><span
                            class="gnews-amarelo">o</span><span
                            class="gnews-azul">g</span><span
                            class="gnews-verde">l</span><span
                            class="gnews-vermelho">e</span> <span class="gnews-news">News</span></span>
                </span>
                <span class="gnews-nota">{{ __('site.google_news.nota') }}</span>
            </span>

            <span class="gnews-botao">
                {{ __('site.google_news.botao') }} <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i>
            </span>
        </a>
    </div>
</div>

@once
<style>
    .gnews-cartao {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: .9rem 1.25rem;
        border: 1px solid #e3e8ef;
        border-radius: .75rem;
        background-color: #fff;
        box-shadow: 0 1px 2px rgba(16, 24, 40, .05);
        text-decoration: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .gnews-cartao:hover,
    .gnews-cartao:focus-visible {
        border-color: #4285f4;
        box-shadow: 0 .5rem 1.25rem rgba(66, 133, 244, .15);
    }

    .gnews-icone {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.75rem;
        height: 2.75rem;
        border-radius: .6rem;
        background-color: #eef4ff;
        color: #4285f4;
        font-size: 1.4rem;
    }

    .gnews-texto {
        flex: 1 1 auto;
        min-width: 0;
    }

    .gnews-titulo {
        display: block;
        color: #1f2d3d;
        font-weight: 700;
        line-height: 1.35;
    }

    .gnews-nota {
        display: block;
        margin-top: .1rem;
        color: #64748b;
        font-size: .85rem;
    }

    /* O logótipo do Google News, escrito com as cores da marca. */
    .gnews-marca {
        font-family: Arial, Helvetica, sans-serif;
        font-weight: 700;
        white-space: nowrap;
    }

    .gnews-azul { color: #4285f4; }
    .gnews-vermelho { color: #ea4335; }
    .gnews-amarelo { color: #fbbc05; }
    .gnews-verde { color: #34a853; }
    .gnews-news { color: #5f6368; font-weight: 400; }

    .gnews-botao {
        flex: 0 0 auto;
        padding: .45rem 1.1rem;
        border-radius: 2rem;
        background-color: #4285f4;
        color: #fff;
        font-weight: 700;
        font-size: .9rem;
        white-space: nowrap;
    }

    .gnews-cartao:hover .gnews-botao,
    .gnews-cartao:focus-visible .gnews-botao { background-color: #1a73e8; }

    @media (max-width: 575.98px) {
        .gnews-cartao {
            flex-wrap: wrap;
            gap: .75rem;
        }

        .gnews-texto { flex-basis: calc(100% - 3.5rem); }
        .gnews-botao { width: 100%; text-align: center; }
    }

    @media (prefers-reduced-motion: reduce) {
        .gnews-cartao { transition: none; }
    }

    @media print {
        .gnews { display: none; }
    }
</style>
@endonce

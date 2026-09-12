@extends('templates.app')
@section('title', __('site.home.meta_titulo'))
@section('description', __('site.home.meta_descricao'))
@section('canonical_link', url('/'))

@section('head-scripts')
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebPage",
      "name": "Angola Emprego — Vagas de Emprego, Notícias e Cursos",
      "description": "Angola Emprego é o maior portal de emprego em Angola. Encontre vagas de trabalho, cursos gratuitos e notícias de carreira.",
      "url": "{{ url('/') }}",
      "inLanguage": "{{ app()->getLocale() === 'pt' ? 'pt-AO' : app()->getLocale() }}",
      "isPartOf": {
        "@type": "WebSite",
        "name": "Angola Emprego",
        "url": "{{ url('/') }}"
      }
    }
    </script>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "ItemList",
      "name": "Vagas de Emprego em Destaque",
      "itemListOrder": "https://schema.org/ItemListOrderDescending",
      "numberOfItems": {{ $jobs->count() }},
      "itemListElement": [
        @foreach($jobs as $index => $job)
            {
              "@type": "ListItem",
              "position": {{ $index + 1 }},
              "url": "{{ url('/vagas/' . $job->slug) }}",
              "name": "{{ $job->title }} — {{ $job->company }}"
            }@if(!$loop->last),@endif
        @endforeach
      ]
    }
    </script>
@endsection

@section('content')
    @if(session('success'))
        <div class="container pt-4">
            <div class="alert alert-success mb-0">{{ session('success') }}</div>
        </div>
    @endif
    <!-- Hero Section -->
    <section id="hero" class="hero section d-flex align-items-center"
        style="min-height: 60vh; background: linear-gradient(135deg, #f3f2f1 0%, #ffffff 100%);">

        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold text-dark mb-4">{{ __('site.home.hero_titulo') }}</h1>
                    <p class="lead text-muted mb-5">{{ __('site.home.hero_subtitulo') }}</p>

                    <div class="bg-white p-4 rounded-3 shadow-sm border">
                        <form action="{{ url('/vagas') }}" method="GET" class="row g-3">
                            <div class="col-md-5">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i
                                            class="bi bi-search text-muted"></i></span>
                                    <input type="text" name="q" class="form-control border-start-0 ps-0"
                                        placeholder="{{ __('site.home.pesquisa_cargo') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i
                                            class="bi bi-geo-alt text-muted"></i></span>
                                    <input type="text" name="location" class="form-control border-start-0 ps-0"
                                        placeholder="{{ __('site.home.pesquisa_local') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100 fw-bold">{{ __('site.home.pesquisar') }}</button>
                            </div>
                        </form>
                    </div>

                    <div class="mt-4 text-muted small">
                        <span class="me-2">{{ __('site.home.tendencias') }}</span>
                        <a href="#"
                            class="text-decoration-none text-muted me-2 bg-light px-2 py-1 rounded border">{{ __('site.home.tendencia_tecnologia') }}</a>
                        <a href="#"
                            class="text-decoration-none text-muted me-2 bg-light px-2 py-1 rounded border">{{ __('site.home.tendencia_vendas') }}</a>
                        <a href="#"
                            class="text-decoration-none text-muted me-2 bg-light px-2 py-1 rounded border">{{ __('site.home.tendencia_construcao') }}</a>
                    </div>
                </div>
            </div>
        </div>

    </section><!-- /Hero Section -->

    <!-- ═══ [CV IA PROMO] SECÇÃO ANÁLISE DE CV POR IA PARA EMPRESAS ═══ -->
    <section class="py-5 bg-white border-bottom" id="cv-ai-promo-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="cv-ai-promo-card">
                        <div class="row align-items-center g-0">
                            <div class="col-lg-7 p-4 p-lg-5">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge bg-danger rounded-pill px-3 py-2 fw-bold cv-ai-badge-pulse">
                                        <i class="bi bi-stars me-1"></i> {{ __('site.home.ia_novo') }}
                                    </span>
                                    <span class="text-muted small">{{ __('site.home.ia_gratuito') }}</span>
                                </div>
                                <h2 class="fw-bold text-dark mb-3" style="line-height: 1.3;">{{ __('site.home.ia_titulo') }}</h2>
                                <p class="text-muted mb-4">{{ __('site.home.ia_texto') }}</p>
                                <div class="d-flex flex-wrap gap-3 mb-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="cv-ai-stat-mini bg-primary bg-opacity-10 text-primary">
                                            <i class="bi bi-building"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold small">{{ __('site.home.ia_cadastro') }}</div>
                                            <div class="text-muted" style="font-size:0.7rem;">{{ __('site.home.ia_cadastro_nota') }}</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="cv-ai-stat-mini bg-success bg-opacity-10 text-success">
                                            <i class="bi bi-file-earmark-text-fill"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold small">{{ __('site.home.ia_leitura') }}</div>
                                            <div class="text-muted" style="font-size:0.7rem;">{{ __('site.home.ia_leitura_nota') }}</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="cv-ai-stat-mini bg-warning bg-opacity-10 text-warning">
                                            <i class="bi bi-bar-chart-fill"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold small">{{ __('site.home.ia_ranking') }}</div>
                                            <div class="text-muted" style="font-size:0.7rem;">{{ __('site.home.ia_ranking_nota') }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-3 align-items-center">
                                    <a href="{{ route('register.company') }}"
                                        class="btn btn-primary btn-lg rounded-pill fw-bold px-4 shadow-sm">
                                        <i class="bi bi-building-add me-2"></i> {{ __('site.home.ia_cadastrar') }}
                                    </a>
                                    <a href="{{ route('cv-analysis.info') }}" class="fw-bold text-decoration-none">
                                        {{ __('site.home.ia_como_funciona') }} <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                            <div
                                class="col-lg-5 d-none d-lg-flex align-items-center justify-content-center cv-ai-promo-visual">
                                <div class="cv-ai-visual-icon">
                                    <i class="bi bi-robot"></i>
                                </div>
                                <div class="cv-ai-visual-badge badge-1">
                                    <i class="bi bi-check-circle-fill text-success me-1"></i> {{ __('site.home.ia_visual_compativel') }}
                                </div>
                                <div class="cv-ai-visual-badge badge-2">
                                    <i class="bi bi-file-earmark-check-fill text-primary me-1"></i> {{ __('site.home.ia_visual_lido') }}
                                </div>
                                <div class="cv-ai-visual-badge badge-3">
                                    <i class="bi bi-list-ol text-info me-1"></i> {{ __('site.home.ia_visual_ranking') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .cv-ai-promo-card {
                background: #fff;
                border-radius: 16px;
                border: 1px solid #e8e8e8;
                box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
                overflow: hidden;
                transition: box-shadow 0.3s;
            }

            .cv-ai-promo-card:hover {
                box-shadow: 0 8px 40px rgba(37, 87, 167, 0.1);
            }

            .cv-ai-stat-mini {
                width: 36px;
                height: 36px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.9rem;
            }

            .cv-ai-promo-visual {
                position: relative;
                min-height: 280px;
                background: linear-gradient(135deg, #eef2ff 0%, #e8f0fe 100%);
            }

            .cv-ai-visual-icon {
                width: 100px;
                height: 100px;
                border-radius: 24px;
                background: linear-gradient(135deg, #2557a7 0%, #4f73c9 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 2.5rem;
                color: #fff;
                box-shadow: 0 8px 24px rgba(37, 87, 167, 0.3);
            }

            .cv-ai-visual-badge {
                position: absolute;
                background: #fff;
                border-radius: 50px;
                padding: 8px 16px;
                font-size: 0.8rem;
                font-weight: 600;
                box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
                animation: cv-ai-float 3s ease-in-out infinite;
            }

            .cv-ai-visual-badge.badge-1 {
                top: 30px;
                right: 30px;
                animation-delay: 0s;
            }

            .cv-ai-visual-badge.badge-2 {
                bottom: 40px;
                left: 20px;
                animation-delay: 0.5s;
            }

            .cv-ai-visual-badge.badge-3 {
                top: 50%;
                right: 15px;
                animation-delay: 1s;
            }

            @keyframes cv-ai-float {

                0%,
                100% {
                    transform: translateY(0);
                }

                50% {
                    transform: translateY(-6px);
                }
            }

            .cv-ai-badge-pulse {
                animation: cv-ai-pulse 2s ease-in-out infinite;
            }

            @keyframes cv-ai-pulse {

                0%,
                100% {
                    opacity: 1;
                }

                50% {
                    opacity: 0.7;
                }
            }
        </style>
    </section>
    <!-- ═══ [/CV IA PROMO] FIM DA SECÇÃO ═══ -->

    <!-- Features Section -->
    <section class="py-5" style="background-color: #fff;">
        <div class="container">
            <div class="row g-4 justify-content-center">
                <div class="col-md-4">
                    <div class="p-4 border rounded-3 h-100 bg-light-hover transition-all">
                        <i class="bi bi-file-earmark-person text-primary fs-2 mb-3"></i>
                        <h5 class="fw-bold text-dark">{{ __('site.home.cv_titulo') }}</h5>
                        <p class="text-muted small">{{ __('site.home.cv_texto') }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4 border rounded-3 h-100 bg-light-hover transition-all">
                        <i class="bi bi-bell text-primary fs-2 mb-3"></i>
                        <h5 class="fw-bold text-dark">{{ __('site.home.alertas_titulo') }}</h5>
                        <p class="text-muted small">{{ __('site.home.alertas_texto') }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4 border rounded-3 h-100 bg-light-hover transition-all">
                        <i class="bi bi-mortarboard text-primary fs-2 mb-3"></i>
                        <h5 class="fw-bold text-dark">{{ __('site.home.formacao_titulo') }}</h5>
                        <p class="text-muted small">{{ __('site.home.formacao_texto') }}</p>
                        <a href="{{ route('courses.index') }}" class="small fw-bold text-primary text-decoration-none">{{ __('site.home.ver_cursos') }} <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Job Section -->
    <section id="about" class="about section bg-white py-5">

        <!-- Section Title -->
        <div class="container section-title mb-5 text-center">
            <h2 class="fw-bold text-dark">{{ __('site.home.destaques') }}</h2>
            <p class="text-muted">{{ __('site.home.destaques_subtitulo') }}</p>
        </div><!-- End Section Title -->

        <div class="container">

            <div class="row gy-4">

                @foreach($jobs as $job)
                    <div class="col-md-6 col-lg-4 d-flex align-items-stretch">
                        <a href="{{ url('/vagas/' . $job->slug) }}" class="text-decoration-none w-100">
                            <div class="card h-100 border-0 shadow-sm shadow-hover transition-all p-3"
                                style="border-radius: 12px;">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="card-title fw-bold text-dark mb-0">{{ $job->title }}</h5>
                                        @if($loop->iteration <= 3)
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill small">{{ __('site.home.novo') }}</span>
                                        @endif
                                    </div>
                                    <h6 class="card-subtitle mb-3 text-muted small"><i class="bi bi-building me-1"></i>
                                        {{ $job->company }}</h6>
                                    <div class="mb-3">
                                        <span class="badge bg-light text-dark border me-1"><i class="bi bi-geo-alt me-1"></i>
                                            {{ $job->location }}</span>
                                        <span class="badge bg-light text-dark border"><i class="bi bi-clock me-1"></i> {{ __('site.vagas.integral') }}</span>
                                    </div>
                                    <p class="card-text text-muted small description-truncate">
                                        {!! \Illuminate\Support\Str::limit(strip_tags($job->description), 100, $end = '...') !!}
                                    </p>
                                </div>
                                <div class="card-footer bg-white border-0 pt-0 text-muted small">
                                    {{ __('site.home.publicado_em', ['data' => date_format(new DateTime($job->created_at), 'd-m-Y')]) }}
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach

                <div class="col-12 text-center mt-5">
                    <a href="{{url('/vagas')}}" class="btn btn-primary btn-lg fw-bold px-5 rounded-pill">{{ __('site.home.ver_todas_vagas') }}</a>
                </div>

            </div>

        </div>

    </section><!-- /Job Section -->

    <!-- Recent Articles Section -->
    <section id="blog" class="blog section py-5 bg-light">
        <div class="container section-title mb-5 text-center">
            <h2 class="fw-bold text-dark">{{ __('site.home.noticias') }}</h2>
            <p class="text-muted">{{ __('site.home.noticias_subtitulo') }}</p>
        </div>
        <div class="container">
            <div class="row gy-4">
                @foreach($posts as $post)
                    <div class="col-md-6 col-lg-4 d-flex align-items-stretch">
                        <article class="card h-100 border-0 shadow-sm shadow-hover transition-all w-100"
                            style="border-radius: 12px; overflow: hidden;">
                            <a href="{{ url('/noticias/' . $post->slug) }}" class="text-decoration-none text-dark">
                                <div class="post-img overflow-hidden position-relative" style="height: 200px;">
                                    <img src="{{asset('storage/thumb/' . $post->image)}}" alt="{{ $post->title }}"
                                        class="img-fluid w-100 h-100"
                                        style="object-fit: cover; transition: transform 0.5s ease;">
                                </div>

                                <div class="card-body p-4">
                                    <div class="post-meta mb-2 small text-muted">
                                        <span class="me-3"><i class="bi bi-calendar me-1"></i>
                                            {{ date_format(new DateTime($post->created_at), 'd/m/Y') }}</span>
                                    </div>

                                    <h5 class="card-title fw-bold mb-3">{{ $post->title }}</h5>

                                    <p class="card-text text-muted small">
                                        {!! \Illuminate\Support\Str::limit(strip_tags($post->description), 100) !!}
                                    </p>
                                </div>
                                <div class="card-footer bg-white border-0 px-4 pb-4 pt-0">
                                    <span class="text-primary fw-bold small">{{ __('site.home.ler_mais') }} <i class="bi bi-arrow-right ms-1"></i></span>
                                </div>
                            </a>
                        </article>
                    </div>
                @endforeach

                <div class="col-12 text-center mt-5">
                    <a href="{{url('/noticias')}}" class="btn btn-outline-primary btn-lg fw-bold px-5 rounded-pill">{{ __('site.home.ver_mais_noticias') }}</a>
                </div>
            </div>
        </div>
    </section><!-- /Recent Articles Section -->

    <!-- Social Media CTA Section -->
    @include('partials.social-cta')

    <style>
        .bg-light-hover:hover {
            background-color: #f8f9fa;
        }

        .shadow-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, .1) !important;
        }

        .transition-all {
            transition: all 0.3s ease;
        }
    </style>
@endsection('content')
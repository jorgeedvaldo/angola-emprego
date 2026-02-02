@extends('templates.app')
@section('title', 'Início')
@section('description', 'Angola Emprego é o maior portal de emprego em Angola, comprometido em ajudar milhares de angolanos a encontrar as melhores oportunidades de trabalho diariamente')
@section('canonical_link', url('/'))

@section('head-scripts')

@endsection

@section('content')
    <!-- Hero Section -->
    <section id="hero" class="hero section d-flex align-items-center" style="min-height: 60vh; background: linear-gradient(135deg, #f3f2f1 0%, #ffffff 100%);">

        <div class="container">
            <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold text-dark mb-4">Somos o maior portal de empregos em Angola</h1>
                <p class="lead text-muted mb-5">Milhares de oportunidades de emprego e formação em Angola à sua espera.</p>
                
                <div class="bg-white p-4 rounded-3 shadow-sm border">
                    <form action="{{ url('/vagas') }}" method="GET" class="row g-3">
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Cargo, empresa ou competências">
                            </div>
                        </div>
                        <div class="col-md-4">
                             <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-geo-alt text-muted"></i></span>
                                <input type="text" name="location" class="form-control border-start-0 ps-0" placeholder="Cidade ou Província">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100 fw-bold">Pesquisar Vagas</button>
                        </div>
                    </form>
                </div>
                
                <div class="mt-4 text-muted small">
                    <span class="me-2">Tendências:</span>
                    <a href="#" class="text-decoration-none text-muted me-2 bg-light px-2 py-1 rounded border">Tecnologia</a>
                    <a href="#" class="text-decoration-none text-muted me-2 bg-light px-2 py-1 rounded border">Vendas</a>
                    <a href="#" class="text-decoration-none text-muted me-2 bg-light px-2 py-1 rounded border">Construção</a>
                </div>
            </div>
            </div>
        </div>

    </section><!-- /Hero Section -->

    <!-- Features Section -->
    <section class="py-5" style="background-color: #fff;">
      <div class="container">
        <div class="row g-4 justify-content-center">
          <div class="col-md-4">
            <div class="p-4 border rounded-3 h-100 bg-light-hover transition-all">
                <i class="bi bi-file-earmark-person text-primary fs-2 mb-3"></i>
                <h5 class="fw-bold text-dark">Melhore o seu CV</h5>
                <p class="text-muted small">Receba dicas para tornar o seu currículo mais atraente para os recrutadores.</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-4 border rounded-3 h-100 bg-light-hover transition-all">
                <i class="bi bi-bell text-primary fs-2 mb-3"></i>
                <h5 class="fw-bold text-dark">Alertas de Vagas</h5>
                <p class="text-muted small">Seja notificado assim que novas vagas compatíveis com seu perfil forem publicadas.</p>
            </div>
          </div>
          <div class="col-md-4">
             <div class="p-4 border rounded-3 h-100 bg-light-hover transition-all">
                <i class="bi bi-mortarboard text-primary fs-2 mb-3"></i>
                <h5 class="fw-bold text-dark">Desenvolvimento</h5>
                <p class="text-muted small">Aceda aos nossos cursos gratuitos e desenvolva novas competências.</p>
                <a href="{{ route('courses.index') }}" class="small fw-bold text-primary text-decoration-none">Ver Cursos <i class="bi bi-arrow-right"></i></a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Cloud Service Explanation Section -->
    <section class="py-5 bg-white border-bottom">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold text-dark">Como Funcionam as Candidaturas Automáticas?</h2>
                <p class="text-muted lead">Deixe que nós tratamos de tudo por si. Veja como é simples:</p>
            </div>

            <div class="row g-4 text-center justify-content-center">
                <div class="col-md-4">
                    <div class="p-4">
                         <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-file-person fs-1"></i>
                        </div>
                        <h5 class="fw-bold">1. Análise de Perfil</h5>
                        <p class="text-muted">Com base no seu CV e preferências, identificamos as vagas que combinam perfeitamente com o seu perfil.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-send-check fs-1"></i>
                        </div>
                        <h5 class="fw-bold">2. Candidatura Automática</h5>
                        <p class="text-muted">Sempre que surgir uma nova vaga na sua área (ex: Motorista, Vendas), nós enviamos a sua candidatura automaticamente.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-clipboard-data fs-1"></i>
                        </div>
                        <h5 class="fw-bold">3. Relatórios Periódicos</h5>
                        <p class="text-muted">Receba periodicamente um relatório detalhado de todas as candidaturas que efectuamos em seu nome.</p>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center mt-4">
                <div class="col-lg-8">
                    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center p-3" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-3 me-3 text-warning"></i>
                        <div>
                            <strong>Nota Importante:</strong> Este serviço garante o envio das suas candidaturas aos nossos clientes, mas <span class="text-decoration-underline">não garante a obtenção de emprego</span>. O recrutamento depende sempre da avaliação da empresa empregadora.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Subscription Plans Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold text-dark">Candidaturas Automáticas</h2>
                <p class="text-muted lead">Aumente as suas chances deixando que nós apliquemos por si.</p>
            </div>

            <div class="row row-cols-1 row-cols-md-4 mb-3 text-center">
                <!-- Semanal -->
                <div class="col">
                  <div class="card mb-4 rounded-3 shadow-sm border-0 h-100">
                    <div class="card-header py-3 bg-white border-0">
                      <h4 class="my-0 fw-normal">Semanal</h4>
                    </div>
                    <div class="card-body d-flex flex-column">
                      <h1 class="card-title pricing-card-title">1.000 <small class="text-muted fw-light">Kz</small></h1>
                      <ul class="list-unstyled mt-3 mb-4 flex-grow-1">
                        <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Candidaturas Automáticas</li>
                        <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Válido por 7 dias</li>
                        <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Suporte Prioritário</li>
                      </ul>
                      <a href="{{ route('plans.confirm', ['plan' => 'weekly']) }}" class="w-100 btn btn-lg btn-outline-primary rounded-pill">Escolher Semanal</a>
                    </div>
                  </div>
                </div>
    
                <!-- Mensal -->
                <div class="col">
                  <div class="card mb-4 rounded-3 shadow border-primary h-100 border-2">
                    <div class="card-header py-3 bg-primary text-white border-primary">
                      <h4 class="my-0 fw-normal">Mensal</h4>
                    </div>
                    <div class="card-body d-flex flex-column">
                      <h1 class="card-title pricing-card-title">3.000 <small class="text-muted fw-light">Kz</small></h1>
                      <ul class="list-unstyled mt-3 mb-4 flex-grow-1">
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i>Candidaturas Automáticas</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i>Válido por 30 dias</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i>Destaque no Perfil</li>
                      </ul>
                      <a href="{{ route('plans.confirm', ['plan' => 'monthly']) }}" class="w-100 btn btn-lg btn-primary rounded-pill">Escolher Mensal</a>
                    </div>
                  </div>
                </div>
    
                <!-- Trimestral -->
                <div class="col">
                  <div class="card mb-4 rounded-3 shadow-sm border-0 h-100">
                    <div class="card-header py-3 bg-white border-0">
                      <h4 class="my-0 fw-normal">Trimestral</h4>
                    </div>
                    <div class="card-body d-flex flex-column">
                      <h1 class="card-title pricing-card-title">6.950 <small class="text-muted fw-light">Kz</small></h1>
                      <ul class="list-unstyled mt-3 mb-4 flex-grow-1">
                        <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Candidaturas Automáticas</li>
                        <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Válido por 90 dias</li>
                        <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Poupança de 3.000 Kz</li>
                      </ul>
                       <a href="{{ route('plans.confirm', ['plan' => 'quarterly']) }}" class="w-100 btn btn-lg btn-outline-primary rounded-pill">Escolher Trimestral</a>
                    </div>
                  </div>
                </div>
                
                 <!-- Anual -->
                <div class="col">
                  <div class="card mb-4 rounded-3 shadow-sm border-0 h-100">
                    <div class="card-header py-3 bg-white border-0">
                      <h4 class="my-0 fw-normal">Anual</h4>
                    </div>
                    <div class="card-body d-flex flex-column">
                      <h1 class="card-title pricing-card-title">10.000 <small class="text-muted fw-light">Kz</small></h1>
                      <ul class="list-unstyled mt-3 mb-4 flex-grow-1">
                        <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Candidaturas Automáticas</li>
                        <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Válido por 1 ano</li>
                        <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Poupança Máxima</li>
                      </ul>
                       <a href="{{ route('plans.confirm', ['plan' => 'yearly']) }}" class="w-100 btn btn-lg btn-outline-primary rounded-pill">Escolher Anual</a>
                    </div>
                  </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Job Section -->
    <section id="about" class="about section bg-white py-5">

        <!-- Section Title -->
        <div class="container section-title mb-5 text-center">
          <h2 class="fw-bold text-dark">Destaques</h2>
          <p class="text-muted">As vagas mais recentes em Angola</p>
        </div><!-- End Section Title -->

        <div class="container">

          <div class="row gy-4">

            @foreach($jobs as $job)
              <div class="col-md-6 col-lg-4 d-flex align-items-stretch">
                  <a href="{{ url('/vagas/' . $job->slug) }}" class="text-decoration-none w-100">
                      <div class="card h-100 border-0 shadow-sm shadow-hover transition-all p-3" style="border-radius: 12px;">
                          <div class="card-body">
                              <div class="d-flex justify-content-between align-items-start mb-3">
                                  <h5 class="card-title fw-bold text-dark mb-0">{{ $job->title }}</h5>
                                  @if($loop->iteration <= 3)
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill small">Novo</span>
                                  @endif
                              </div>
                              <h6 class="card-subtitle mb-3 text-muted small"><i class="bi bi-building me-1"></i> {{ $job->company }}</h6>
                              <div class="mb-3">
                                  <span class="badge bg-light text-dark border me-1"><i class="bi bi-geo-alt me-1"></i> {{ $job->location }}</span>
                                  <span class="badge bg-light text-dark border"><i class="bi bi-clock me-1"></i> Integral</span>
                              </div>
                              <p class="card-text text-muted small description-truncate">
                                {!! \Illuminate\Support\Str::limit(strip_tags($job->description), 100, $end='...') !!}
                              </p>
                          </div>
                          <div class="card-footer bg-white border-0 pt-0 text-muted small">
                              Publicado em {{ date_format(new DateTime($job->created_at), 'd-m-Y') }}
                          </div>
                      </div>
                  </a>
              </div>
            @endforeach
            
            <div class="col-12 text-center mt-5">
                 <a href="{{url('/vagas')}}" class="btn btn-primary btn-lg fw-bold px-5 rounded-pill">Ver Todas as Vagas</a>
            </div>
            
          </div>

        </div>

      </section><!-- /Job Section -->

    <!-- Recent Articles Section -->
    <section id="blog" class="blog section py-5 bg-light">
        <div class="container section-title mb-5 text-center">
            <h2 class="fw-bold text-dark">Últimas Notícias</h2>
            <p class="text-muted">Notícias e dicas de carreira para impulsionar o seu sucesso</p>
        </div>
        <div class="container">
            <div class="row gy-4">
               @foreach($posts as $post)
                <div class="col-md-6 col-lg-4 d-flex align-items-stretch">
                  <article class="card h-100 border-0 shadow-sm shadow-hover transition-all w-100" style="border-radius: 12px; overflow: hidden;">
                    <a href="{{ url('/noticias/' . $post->slug) }}" class="text-decoration-none text-dark">
                        <div class="post-img overflow-hidden position-relative" style="height: 200px;">
                           <img src="{{asset('storage/thumb/' . $post->image)}}" alt="{{ $post->title }}" class="img-fluid w-100 h-100" style="object-fit: cover; transition: transform 0.5s ease;">
                        </div>

                         <div class="card-body p-4">
                            <div class="post-meta mb-2 small text-muted">
                                <span class="me-3"><i class="bi bi-calendar me-1"></i> {{ date_format(new DateTime($post->created_at), 'd/m/Y') }}</span>
                            </div>

                            <h5 class="card-title fw-bold mb-3">{{ $post->title }}</h5>
                            
                            <p class="card-text text-muted small">
                                {!! \Illuminate\Support\Str::limit(strip_tags($post->description), 100) !!}
                            </p>
                        </div>
                        <div class="card-footer bg-white border-0 px-4 pb-4 pt-0">
                            <span class="text-primary fw-bold small">Ler mais <i class="bi bi-arrow-right ms-1"></i></span>
                        </div>
                    </a>
                  </article>
                </div>
                @endforeach

                <div class="col-12 text-center mt-5">
                     <a href="{{url('/noticias')}}" class="btn btn-outline-primary btn-lg fw-bold px-5 rounded-pill">Ver Mais Notícias</a>
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
            box-shadow: 0 1rem 3rem rgba(0,0,0,.1)!important;
        }
        .transition-all {
            transition: all 0.3s ease;
        }
      </style>
@endsection('content')

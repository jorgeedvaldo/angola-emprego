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
            <div class="col-lg-8" data-aos="zoom-out">
                <h1 class="display-4 fw-bold text-dark mb-4">Encontre o emprego ideal para si</h1>
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

    <!-- Job Section -->
    <section id="about" class="about section bg-light py-5">

        <!-- Section Title -->
        <div class="container section-title mb-5 text-center" data-aos="fade-up">
          <h2 class="fw-bold text-dark">Destaques</h2>
          <p class="text-muted">As vagas mais recentes em Angola</p>
        </div><!-- End Section Title -->

        <div class="container">

          <div class="row gy-4">

            @foreach($jobs as $job)
              <div class="col-md-6 col-lg-4 d-flex align-items-stretch" data-aos="fade-up">
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

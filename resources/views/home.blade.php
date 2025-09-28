@extends('templates.app')
@section('title', 'Início')
@section('description', 'Angola Emprego é o maior portal de emprego em Angola, comprometido em ajudar milhares de angolanos a encontrar as melhores oportunidades de trabalho diariamente')
@section('canonical_link', url('/'))

@section('head-scripts')

@endsection

@section('content')
    <!-- Hero Section -->
    <section id="hero" class="hero section light-background">

        <div class="container">
            <div class="row gy-4">
            <div class="col-lg-6 order-2 order-lg-1 d-flex flex-column justify-content-center" data-aos="zoom-out">
                <h1>Bem-vindo ao <span>AngolaEmprego</span></h1>
                <p>O Maior portal de emprego em Angola</p>
                <div class="d-flex">
                <a href="{{url('/vagas')}}" class="btn-get-started">Ver Oportunidades</a>
                </div>
            </div>
            </div>
        </div>

    </section><!-- /Hero Section -->

    <section class="py-5 px-5" style="background-color: #f8f9fa;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="text-center mb-5">
          <h2 class="display-5 fw-bold text-dark mb-3">
            Candidaturas <span style="color: #007bff;">Automáticas</span>
          </h2>
          <p class="lead text-muted">
            Deixe que nós façamos as candidaturas por você! Com base no seu CV, aplicamos automaticamente às vagas que combinam com o seu perfil.
          </p>
        </div>

        <div class="row g-4 mb-5">
          <div class="col-md-4">
            <div class="text-center">
              <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                <i class="fas fa-robot text-primary" style="font-size: 2rem;"></i>
              </div>
              <h5 class="fw-bold text-dark">Automação Inteligente</h5>
              <p class="text-muted small">Sistema avançado que analisa seu perfil e encontra as melhores oportunidades.</p>
            </div>
          </div>

          <div class="col-md-4">
            <div class="text-center">
              <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                <i class="fas fa-clock text-primary" style="font-size: 2rem;"></i>
              </div>
              <h5 class="fw-bold text-dark">Economia de Tempo</h5>
              <p class="text-muted small">Não perca mais tempo procurando vagas. Focamos nas que realmente importam</p>
            </div>
          </div>

          <div class="col-md-4">
            <div class="text-center">
              <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                <i class="fas fa-target text-primary" style="font-size: 2rem;"></i>
              </div>
              <h5 class="fw-bold text-dark">Precisão Máxima</h5>
              <p class="text-muted small">Candidaturas direcionadas baseadas na sua experiência e objetivos</p>
            </div>
          </div>
        </div>

        <div class="text-center">
          <a href="https://pay.kuenha.com/856ed35c-7b33-4e98-9352-954d22bc56a2" class="btn btn-primary btn-lg px-5 py-3 rounded-pill shadow-sm">
            <i class="fas fa-eye me-2"></i>Ver Planos
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

    <!-- Job Section -->
    <section id="about" class="about section light-background">

        <!-- Section Title -->
        <div class="container section-title" data-aos="fade-up">
          <h2>Empregos</h2>
          <p><span>Encontre mais oportunidades de</span> <span class="description-title">emprego</span></p>
        </div><!-- End Section Title -->

        <div class="container">

          <div class="row gy-3">

            @foreach($jobs as $job)
              <!-- Primeiro Card -->
              <div class="col-md-6 mb-4">
                  <div class="card h-100">
                      <div class="card-header bg-primary text-white">
                          <h5 class="card-title mb-0">{{ $job->title }}</h5>
                      </div>
                      <div class="card-body">
                          <h6 class="card-subtitle mb-2 text-muted">{{ $job->company }}</h6>
                          <p class="card-text">
                            {!! \Illuminate\Support\Str::limit(strip_tags($job->description), 207, $end='...') !!}
                          </p>
                          <ul class="list-unstyled">
                              <li><strong>Local:</strong> {{ $job->location }}</li>
                          </ul>
                          <a href="{{ url('/vagas/' . $job->slug) }}" class="btn btn-primary">Ver Detalhes</a>
                      </div>
                      <div class="card-footer text-muted">
                          Publicado em {{ date_format(new DateTime($job->created_at), 'd-m-Y') }}
                      </div>
                  </div>
              </div>
            @endforeach
            <a href="{{url('/vagas')}}" class="btn btn-outline-primary btn-lg">Ver mais Vagas</a>
          </div>

        </div>

      </section><!-- /Job Section -->

      <!-- /Blog Posts Section -->
      <section id="blog-posts" class="blog-posts section">

          <!-- Section Title -->
          <div class="container section-title" data-aos="fade-up">
              <h2>Blog</h2>
              <p><span>Últimas novidades do nosso</span> <span class="description-title">Blog</span></p>
          </div><!-- End Section Title -->

          <div class="container">
            <div class="row gy-4">

              @foreach($posts as $post)
              <div class="col-lg-4">
                <article>

                  <div class="post-img mb-3">
                    <img src="{{asset('storage/thumb/' . $post->image)}}" alt="" class="img-fluid">
                  </div>

                  <!-- Category here <p class="post-category">Politics</p>-->

                  <h4 class="title">
                    <a href="{{ url('/' . $post->slug) }}">{{ $post->title }}</a>
                  </h4>

                  <div class="d-flex align-items-center">
                    <div class="post-meta">
                      <p class="post-author">Yuri Kiluanji</p>
                      <p class="post-date">
                        <time datetime="2022-01-01">{{ date_format(new DateTime($post->created_at), 'd-m-Y') }}</time>
                      </p>
                    </div>
                  </div>

                </article>
              </div><!-- End post list item -->
              @endforeach
              <a href="{{url('/blog')}}" class="btn btn-outline-primary btn-lg">Visitar o Blog</a>
            </div>
          </div>

        </section><!-- /Blog Posts Section -->
@endsection('content')

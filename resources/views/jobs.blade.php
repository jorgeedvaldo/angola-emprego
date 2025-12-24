@extends('templates.app')
@section('title', 'Vagas de Emprego')
@section('description', 'Angola Emprego é o maior portal de emprego em Angola, comprometido em ajudar milhares de angolanos a encontrar as melhores oportunidades de trabalho diariamente')
@section('canonical_link', url('/vagas'))

@section('head-scripts')

@endsection

@section('content')
    <!-- Page Title -->
    <div class="bg-light py-5">
      <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                 <h1 class="fw-bold mb-2 text-dark">Vagas de Emprego</h1>
                 <p class="text-muted mb-0">Explore as melhores oportunidades de carreira em Angola.</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                 <nav aria-label="breadcrumb">
                  <ol class="breadcrumb justify-content-lg-end mb-0">
                    <li class="breadcrumb-item"><a href="{{url('/')}}">Início</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Vagas</li>
                  </ol>
                </nav>
            </div>
        </div>
      </div>
    </div><!-- End Page Title -->

    <!-- Jobs Section -->
    <section class="section py-5">
        <div class="container">
            <div class="row gy-4">
                
                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Search Widget -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3">Pesquisar</h5>
                            <form action="{{ url('/vagas') }}" method="GET">
                                <div class="mb-3">
                                    <label class="form-label small text-muted">O que procura?</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                                        <input type="text" name="q" class="form-control border-start-0" placeholder="Cargo, empresa..." value="{{ request('q') }}">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small text-muted">Localização</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-geo-alt"></i></span>
                                        <input type="text" name="location" class="form-control border-start-0" placeholder="Cidade ou Província" value="{{ request('location') }}">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 fw-bold">Filtrar Vagas</button>
                                @if(request()->hasAny(['q', 'location', 'category']))
                                    <a href="{{ url('/vagas') }}" class="btn btn-link text-decoration-none w-100 mt-2 small">Limpar Filtros</a>
                                @endif
                            </form>
                        </div>
                    </div>

                    <!-- Categories Widget -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3">Categorias</h5>
                            <ul class="list-unstyled mb-0 filter-list">
                                @foreach($categories as $category)
                                    <li class="mb-2">
                                        <a href="{{ url('/vagas?category=' . $category->slug) }}" class="text-decoration-none d-flex justify-content-between align-items-center {{ request('category') == $category->slug ? 'fw-bold text-primary' : 'text-muted' }}">
                                            <span>{{ $category->name }}</span>
                                            <span class="badge bg-light text-muted">{{ $category->jobs_count ?? 0 }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <!-- Top Companies Widget -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3">Principais Empresas</h5>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($topCompanies as $companyJob)
                                    <a href="{{ url('/vagas?q=' . $companyJob->company) }}" class="badge bg-light text-dark border text-decoration-none p-2 mb-1 company-badge">
                                        {{ $companyJob->company }} <span class="text-muted ms-1">({{ $companyJob->total }})</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-lg-8">
                     @if(request('q') || request('location') || request('category'))
                        <div class="mb-4">
                            <h5 class="fw-bold">Resultados da pesquisa</h5>
                            <p class="text-muted small">Encontrados {{ $jobs->total() }} resultados</p>
                        </div>
                    @endif

                    <div class="row gy-3">
                      @foreach($jobs as $job)
                        <div class="col-12">
                            <a href="{{ url('/vagas/' . $job->slug) }}" class="text-decoration-none w-100">
                              <div class="card border-0 shadow-sm shadow-hover transition-all p-3" style="border-radius: 12px;">
                                  <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-start">
                                      <div class="mb-3 mb-md-0">
                                          <h5 class="card-title fw-bold text-dark mb-1">{{ $job->title }}</h5>
                                          <div class="text-muted small mb-2"><i class="bi bi-building me-1"></i> {{ $job->company }}</div>
                                          <div class="mb-2">
                                              <span class="badge bg-light text-dark border me-1"><i class="bi bi-geo-alt me-1"></i> {{ $job->location }}</span>
                                              <span class="badge bg-light text-dark border"><i class="bi bi-clock me-1"></i> Integral</span>
                                          </div>
                                           <div class="text-muted small description-truncate" style="max-height: 44px; overflow: hidden;">
                                            {!! \Illuminate\Support\Str::limit(strip_tags($job->description), 140, $end='...') !!}
                                          </div>
                                      </div>
                                      <div class="text-md-end ms-md-4 mt-3 mt-md-0 d-flex flex-column align-items-md-end w-100 w-md-auto">
                                            <span class="text-muted x-small mb-2 text-nowrap"><i class="bi bi-calendar3 me-1"></i> {{ date_format(new DateTime($job->created_at), 'd/m/Y') }}</span>
                                           <span class="btn btn-outline-primary btn-sm fw-bold rounded-pill text-nowrap px-3">Ver Detalhes</span>
                                      </div>
                                  </div>
                              </div>
                            </a>
                        </div>
                      @endforeach

                      @if($jobs->count() == 0)
                        <div class="col-12 text-center py-5">
                            <i class="bi bi-search fs-1 text-muted opacity-50"></i>
                            <h4 class="mt-3 text-muted">Nenhuma vaga encontrada</h4>
                            <p class="text-muted mb-0">Tente ajustar os filtros ou limpar a pesquisa.</p>
                            <a href="{{ url('/vagas') }}" class="btn btn-primary mt-3">Ver Todas as Vagas</a>
                        </div>
                      @endif
                    </div>
                      
                    <div class="mt-5 d-flex justify-content-center">
                        {{ $jobs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section><!-- /Jobs Section -->
    
    <style>
        .shadow-hover:hover {
            transform: translateY(-3px);
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.08)!important;
        }
        .transition-all {
            transition: all 0.2s ease;
        }
        .filter-list a:hover {
            color: var(--primary-color) !important;
            padding-left: 5px;
            transition: all 0.2s;
        }
        .company-badge:hover {
            background-color: var(--primary-color) !important;
            color: #fff !important;
            border-color: var(--primary-color) !important;
        }
        .company-badge:hover .text-muted {
            color: rgba(255,255,255,0.8) !important;
        }
    </style>
@endsection('content')

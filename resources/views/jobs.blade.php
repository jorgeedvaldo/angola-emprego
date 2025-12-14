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

              @foreach($jobs as $job)
                <div class="col-md-6 col-lg-4 d-flex align-items-stretch">
                    <a href="{{ url('/vagas/' . $job->slug) }}" class="text-decoration-none w-100">
                      <div class="card h-100 border-0 shadow-sm shadow-hover transition-all p-3" style="border-radius: 12px;">
                          <div class="card-body">
                              <div class="d-flex justify-content-between align-items-start mb-3">
                                  <h5 class="card-title fw-bold text-dark mb-0">{{ $job->title }}</h5>
                                  <span class="badge bg-light text-dark border">{{ $job->location }}</span>
                              </div>
                              <h6 class="card-subtitle mb-3 text-muted small"><i class="bi bi-building me-1"></i> {{ $job->company }}</h6>
                              
                              <p class="card-text text-muted small description-truncate mb-4">
                                {!! \Illuminate\Support\Str::limit(strip_tags($job->description), 120, $end='...') !!}
                              </p>
                              
                              <div class="d-grid">
                                  <span class="btn btn-outline-primary btn-sm fw-bold rounded-pill">Ver Detalhes</span>
                              </div>
                          </div>
                          <div class="card-footer bg-white border-0 pt-0 text-muted small text-end">
                              <i class="bi bi-clock me-1"></i> {{ date_format(new DateTime($job->created_at), 'd/m/Y') }}
                          </div>
                      </div>
                    </a>
                </div>
              @endforeach
              
              <div class="col-12 mt-5">
                 <div class="d-flex justify-content-center">
                    {{ $jobs->links() }}
                 </div>
              </div>
              
            </div>

          </div>

    </section><!-- /Jobs Section -->
    
    <style>
        .shadow-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(0,0,0,.1)!important;
        }
        .transition-all {
            transition: all 0.3s ease;
        }
    </style>
@endsection('content')

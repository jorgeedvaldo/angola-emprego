@extends('templates.app')
@section('title', 'Vagas de Emprego')
@section('description', 'Angola Emprego é o maior portal de emprego em Angola, comprometido em ajudar milhares de angolanos a encontrar as melhores oportunidades de trabalho diariamente')
@section('canonical_link', url('/vagas'))

@section('head-scripts')

@endsection

@section('content')
    <!-- Page Title -->
    <div class="page-title" data-aos="fade">
      <div class="container d-lg-flex justify-content-between align-items-center">
        <h1 class="mb-2 mb-lg-0">Vagas de Emprego</h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="{{url('/')}}">Início</a></li>
            <li class="current">Vagas</li>
          </ol>
        </nav>
      </div>
    </div><!-- End Page Title -->

    <!-- Service Details Section -->
    <section id="service-details" class="service-details section">

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
            </div>

          </div>

    </section><!-- /Service Details Section -->
@endsection('content')

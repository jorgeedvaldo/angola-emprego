@extends('templates.app')
@section('title', $job->title)
@section('description', strip_tags($job['description']))
@section('canonical_link', url('/vagas/'.$job->slug))
@section('created_at', $job->created_at)
@section('updated_at', $job->updated_at)

@section('head-scripts')

@endsection

@section('content')
    <!-- Page Title -->
    <div class="page-title" data-aos="fade">
      <div class="container d-lg-flex justify-content-between align-items-center">
        <h1 class="mb-2 mb-lg-0">Detalhes da Vaga</h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="{{url('/')}}">Início</a></li>
            <li><a href="{{url('/vagas')}}">Vagas</a></li>
            <li class="current">{{$job->title}}</li>
          </ol>
        </nav>
      </div>
    </div><!-- End Page Title -->

    <!-- Service Details Section -->
    <section id="service-details" class="service-details section">

      <div class="container">

        <div class="row gy-4">

          <div class="col-lg-8" data-aos="fade-up" data-aos-delay="200">
            <img src="{{asset('storage/' . $job->image)}}" alt="" class="img-fluid services-img">
            <h3>{{$job->title}}</h3>
            {!!$job->description!!}
          </div>

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
            <h4>Categorias</h4>
            @foreach($categories as $item)
              <a href="{{ url('/categories/' . $item['id']) }}"><span class="badge text-bg-dark">{{ $item->name }}</span></a>
            @endforeach

            <h4 class="mt-5">Vagas Recentes</h4>
            <div class="services-list">
              @foreach($LastJobs as $item)
                <a href="{{ url('/vagas/' . $item->slug) }}">{{ $item->title }}</a>
              @endforeach
              </div>

          </div>

        </div>

      </div>

    </section><!-- /Service Details Section -->
@endsection('content')

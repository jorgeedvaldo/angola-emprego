@extends('templates.app')
@section('title', $post->title)
@section('description', strip_tags($post->description))
@section('canonical_link', url('/'.$post->slug))
@section('created_at', $post->created_at)
@section('updated_at', $post->updated_at)

@section('head-scripts')

@endsection

@section('content')
    <!-- Page Title -->
    <div class="page-title" data-aos="fade">
      <div class="container d-lg-flex justify-content-between align-items-center">
        <h1 class="mb-2 mb-lg-0">Detalhes do Artigo</h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="{{url('/')}}">Início</a></li>
            <li><a href="{{url('/blog')}}">Blog</a></li>
            <li class="current">{{$post->title}}</li>
          </ol>
        </nav>
      </div>
    </div><!-- End Page Title -->

    <!-- Service Details Section -->
    <section id="service-details" class="service-details section">

      <div class="container">

        <div class="row gy-4">

          <div class="col-lg-8" data-aos="fade-up" data-aos-delay="200">
            <img src="{{asset('storage/' . $post->image)}}" alt="" class="img-fluid services-img">
            <h3>{{$post->title}}</h3>
            {!!$post->description!!}
          </div>

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
            <h4>Categorias</h4>
            @foreach($categories as $item)
              <a href="{{ url('/categories/' . $item['id']) }}"><span class="badge text-bg-dark">{{ $item->name }}</span></a>
            @endforeach

            <h4 class="mt-5">Artigos Recentes</h4>
            <div class="services-list">
              @foreach($LastPosts as $item)
                <a href="{{ url('/' . $item->slug) }}">{{ $item->title }}</a>
              @endforeach
              </div>

          </div>

        </div>

      </div>

    </section><!-- /Service Details Section -->
@endsection('content')

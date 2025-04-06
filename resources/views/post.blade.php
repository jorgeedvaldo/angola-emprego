@extends('templates.app')
@section('title', $post->title)
@section('description', strip_tags($post->description))
@section('canonical_link', url('/'.$post->slug))
@section('created_at', $post->created_at)
@section('updated_at', $post->updated_at)
@section('url', asset('storage/' . $post->image))

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
            <!-- Adição dos novos elementos -->
            <div class="vaga-meta mb-4">
                <!-- Botões de compartilhamento -->
                <div class="compartilhar-botoes mb-2">
                    <a class="btn btn-sm btn-outline-primary me-2" href="https://www.facebook.com/sharer/sharer.php?u={{ url('/'. $post->slug) }}&quote={{ $post->title }}%0A.%0ASe%20você%20deseja%20saber%20mais%20sobre%20esta%20matéria,%20por%20favor,%20clique%20no%20link:%20{{ url('/empregos/'. $post->slug) }}%0A."
                    target="_blank">
                        <i class="bi bi-facebook"></i> Facebook
                    </a>
                    <a class="btn btn-sm btn-outline-primary me-2" href="https://www.linkedin.com/sharing/share-offsite/?url={{ url('/'. $post->slug) }}&text={{ $post->title }}%0A.%0ASe%20você%20deseja%20saber%20mais%20sobre%20esta%20matéria,%20por%20favor,%20clique%20no%20link:%20{{ url('/empregos/'. $post->slug) }}%0A."
                    target="_blank">
                        <i class="bi bi-linkedin"></i> LinkedIn
                    </a>
                    <a class="btn btn-sm btn-outline-success me-2" href="https://api.whatsapp.com/send?text={{ $post->title }}%0A.%0ASe%20você%20deseja%20saber%20mais%20sobre%20esta%20matéria,%20por%20favor,%20clique%20no%20link:%20{{ url('/'. $post->slug) }}%0A."
                    target="_blank">
                        <i class="bi bi-whatsapp"></i> WhatsApp
                    </a>
                    <a class="btn btn-sm btn-outline-danger" href="mailto:?subject={{ $post->title }}&body=Confira esta matéria:%0A%0A{{ $post->title }}%0A%0APara mais detalhes, acesse: {{ url('/'. $post->slug) }}"
                    target="_blank">
                        <i class="bi bi-envelope"></i> Email
                    </a>
                </div>

                <!-- Informações do artigo -->
                <div class="vaga-info text-muted small">
                    <span class="me-3"><i class="bi bi-calendar me-1"></i> Publicada em: {{ date_format(new DateTime($post->created_at), 'd-m-Y') }}</span>
                    <span><i class="bi bi-person-badge me-1"></i> Autor: Yuri Kiluanji</span>
                </div>
            </div>
            <!-- Fim das adições -->
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

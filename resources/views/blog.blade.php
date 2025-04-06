@extends('templates.app')
@section('title', 'Blog')
@section('description', 'Angola Emprego é o maior portal de emprego em Angola, comprometido em ajudar milhares de angolanos a encontrar as melhores oportunidades de trabalho diariamente')
@section('canonical_link', url('/blog'))
@section('url', asset('storage/' . $post->image))

@section('head-scripts')

@endsection

@section('content')
    <!-- Page Title -->
    <div class="page-title" data-aos="fade">
      <div class="container d-lg-flex justify-content-between align-items-center">
        <h1 class="mb-2 mb-lg-0">O Nosso Blog</h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="{{url('/')}}">Início</a></li>
            <li class="current">Blog</li>
          </ol>
        </nav>
      </div>
    </div><!-- End Page Title -->

    <!-- Service Details Section -->
    <section id="service-details" class="service-details section">

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

              </div>

          </div>

    </section><!-- /Service Details Section -->
@endsection('content')

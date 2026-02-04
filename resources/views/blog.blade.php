@extends('templates.app')
@section('title', 'Notícias de Angola e do mundo')
@section('description', 'Fique atualizado com as últimas notícias de Angola e do mundo, incluindo notícias de emprego, notícias de tecnologia, notícias de política e muito mais.')
@section('canonical_link', url('/noticias'))

@section('head-scripts')

@endsection

@section('content')
    <!-- Page Header -->
    <div class="bg-light py-5">
      <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                 <h1 class="fw-bold mb-2 text-dark">Últimas Notícias</h1>
                 <p class="text-muted mb-0">Notícias, dicas de carreira e atualizações do mercado.</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                 <nav aria-label="breadcrumb">
                  <ol class="breadcrumb justify-content-lg-end mb-0">
                    <li class="breadcrumb-item"><a href="{{url('/')}}">Início</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Notícias</li>
                  </ol>
                </nav>
            </div>
        </div>
      </div>
    </div>

    <!-- Blog Section -->
    <section class="section py-5">

        <div class="container">

            <div class="row gy-4">

                @foreach($posts as $post)
                <div class="col-md-6 col-lg-4 d-flex align-items-stretch">
                  <article class="card h-100 border-0 shadow-sm shadow-hover transition-all w-100" style="border-radius: 12px; overflow: hidden;">
                    <a href="{{ url('/noticias/' . $post->slug) }}" class="text-decoration-none text-dark">
                        <div class="post-img overflow-hidden position-relative" style="height: 200px;">
                           <img src="{{asset('storage/thumb/' . $post->image)}}" alt="{{ $post->title }}" class="img-fluid w-100 h-100" style="object-fit: cover; transition: transform 0.5s ease;">
                           <div class="overlay-hover position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-0"></div>
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
                
                <div class="col-12 mt-5 text-center">
                    {{ $posts->links() }}
                </div>
            </div>

          </div>

    </section>
    
    <style>
        .shadow-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(0,0,0,.1)!important;
        }
        .transition-all {
            transition: all 0.3s ease;
        }
        .post-img:hover img {
            transform: scale(1.1);
        }
    </style>
@endsection('content')

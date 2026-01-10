@extends('templates.app')
@section('title', $post->title)
@section('description', strip_tags($post->description))
@section('canonical_link', url('/'.$post->slug))
@section('og_type', 'article')
@section('created_at', $post->created_at)
@section('updated_at', $post->updated_at)
@section('url', asset('storage/' . $post->image))

@section('head-scripts')

@endsection

@section('content')
    <!-- Page Header -->
    <div class="bg-light py-5">
      <div class="container">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-4">
             <li class="breadcrumb-item"><a href="{{url('/')}}">Início</a></li>
             <li class="breadcrumb-item"><a href="{{url('/blog')}}">Blog</a></li>
             <li class="breadcrumb-item active" aria-current="page">{{ \Illuminate\Support\Str::limit($post->title, 50) }}</li>
          </ol>
        </nav>
        
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center">
                <h1 class="display-5 fw-bold text-dark mb-4">{{$post->title}}</h1>
                <div class="d-flex justify-content-center align-items-center gap-3 text-muted">
                     <span><i class="bi bi-person-fill me-1"></i> Yuri Kiluanji</span>
                     <span><i class="bi bi-calendar-event me-1"></i> {{ date_format(new DateTime($post->created_at), 'd/m/Y') }}</span>
                </div>
            </div>
        </div>
      </div>
    </div>

    <!-- Article Section -->
    <section class="section py-5">

      <div class="container">

        <div class="row g-5 justify-content-center">

          <div class="col-lg-8">
            <div class="mb-4 rounded-3 overflow-hidden shadow-sm">
                 <img src="{{asset('storage/' . $post->image)}}" alt="{{ $post->title }}" class="img-fluid w-100">
            </div>

            <div class="bg-white p-lg-5 p-4 rounded-3 text-dark shadow-sm border mb-4 article-content">
                <!-- Botões de compartilhamento -->
                <div class="d-flex gap-2 mb-4">
                    <a class="btn btn-outline-primary btn-sm rounded-pill px-3" href="https://www.facebook.com/sharer/sharer.php?u={{ url('/'. $post->slug) }}" target="_blank"><i class="bi bi-facebook me-1"></i> Partilhar</a>
                    <a class="btn btn-outline-success btn-sm rounded-pill px-3" href="https://api.whatsapp.com/send?text={{ $post->title }} {{ url('/'. $post->slug) }}" target="_blank"><i class="bi bi-whatsapp me-1"></i> WhatsApp</a>
                </div>

                <div class="lead mb-4">
                     {!!$post->description!!}
                </div>
            </div>

          </div>

          <div class="col-lg-4">
            <div class="sticky-top" style="top: 100px;">
                 <div class="card shadow-sm border-0 mb-4 rounded-3">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="fw-bold m-0 text-dark">Categorias</h5>
                    </div>
                    <div class="card-body">
                         <div class="d-flex flex-wrap gap-2">
                            @foreach($categories as $item)
                              <a href="{{ url('/categories/' . $item['id']) }}" class="badge bg-light text-dark border p-2 text-decoration-none">{{ $item->name }}</a>
                            @endforeach
                         </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 rounded-3">
                     <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="fw-bold m-0 text-dark">Artigos Recentes</h5>
                    </div>
                    <div class="list-group list-group-flush">
                      @foreach($LastPosts as $item)
                        <a href="{{ url('/' . $item->slug) }}" class="list-group-item list-group-item-action py-3 border-0 border-bottom">
                            <h6 class="mb-1 fw-bold text-dark">{{ $item->title }}</h6>
                            <small class="text-muted">{{ date_format(new DateTime($item->created_at), 'd/m/Y') }}</small>
                        </a>
                      @endforeach
                      </div>
                </div>
            </div>
          </div>

        </div>

      </div>

    </section>
    
    @include('partials.social-cta')
    
    <style>
        .article-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 20px 0;
        }
        .article-content h2, .article-content h3 {
            font-weight: 700;
            margin-top: 2rem;
            color: #2d2d2d;
        }
    </style>
@endsection('content')

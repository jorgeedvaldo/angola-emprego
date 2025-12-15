@extends('templates.app')
@section('title', $job->title)
@section('description', strip_tags($job['description']))
@section('canonical_link', url('/vagas/'.$job->slug))
@section('created_at', $job->created_at)
@section('updated_at', $job->updated_at)
@section('url', asset('storage/' . $job->image))

@section('head-scripts')
<script type="application/ld+json">
    {
  "@context":"http:\/\/schema.org\/",
  "@type":"JobPosting",
  "datePosted":"{{ date_format(new DateTime($job['created_at']), DATE_ATOM) }}",
  "title":"{{$job['title']}}",
  "description":"{{$job['description']}}",
  "employmentType":["FULL_TIME"],
  "hiringOrganization":{
          "@type":"Organization",
          "name":"{{$job['company']}}",
          "logo":"{{asset('storage/' . $job['image'])}}"
          },
  "identifier":{
          "@type":"PropertyValue",
          "name":"{{$job['company']}}",
          "value":"https:\/\/angolaemprego.com\/#identifier"
          },
  "jobLocation":[

    {
      "@type":"Place",
      "address":"{{$job['province']}}"
    },

    {
        "@type":"Place",
        "address":
                {
                    "@type":"PostalAddress",
                    "streetAddress":"Luanda",
                    "addressLocality":"Luanda",
                    "addressRegion":"Luanda",
                    "postalCode":"Luanda",
                    "addressCountry":"Luanda"
                }
    },
    {
        "@type":"Place",
        "address":
                {
                    "@type":"PostalAddress",
                    "streetAddress":"Angola",
                    "addressLocality":"Angola",
                    "addressRegion":"Angola",
                    "postalCode":"Angola",
                    "addressCountry":"Angola"
                }
    }
]
}
</script>
@endsection

@section('content')
    <!-- Page Header -->
    <div class="bg-light py-5">
      <div class="container">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="{{url('/')}}">Início</a></li>
            <li class="breadcrumb-item"><a href="{{url('/vagas')}}">Vagas</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{$job->title}}</li>
          </ol>
        </nav>
        
        <div class="bg-white p-4 p-lg-5 rounded-3 shadow-sm border">
            <div class="row align-items-center">
                <div class="col-lg-2 mb-3 mb-lg-0 text-center">
                     <img src="{{asset('storage/' . $job->image)}}" alt="{{ $job->company }}" class="img-fluid rounded shadow-sm" style="max-height: 100px; object-fit: contain;">
                </div>
                <div class="col-lg-7">
                    <h1 class="fw-bold text-dark mb-2">{{$job->title}}</h1>
                    <div class="d-flex flex-wrap gap-3 text-muted mb-3">
                         <span><i class="bi bi-building me-1"></i> {{$job->company}}</span>
                         <span><i class="bi bi-geo-alt me-1"></i> {{$job->location ?? 'Angola'}}</span>
                         <span><i class="bi bi-clock me-1"></i> {{ date_format(new DateTime($job['created_at']), 'd/m/Y') }}</span>
                    </div>
                </div>
                <div class="col-lg-3 text-lg-end">
                    <div class="d-grid gap-2">
                        <a href="#apply-section" class="btn btn-primary fw-bold py-2 px-4 rounded-pill">Candidatar-se</a>
                        <button class="btn btn-outline-secondary btn-sm rounded-pill" onclick="window.print()"><i class="bi bi-printer me-1"></i> Imprimir</button>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>

    <!-- Job Details Section -->
    <section class="section py-5">

      <div class="container">

        <div class="row gy-5">

          <div class="col-lg-8">
            
            <div class="bg-white p-4 rounded-3 shadow-sm border mb-4">
                <h4 class="fw-bold mb-4 border-bottom pb-2">Descrição da Vaga</h4>
                <div class="job-description text-dark" style="font-size: 1.05rem; line-height: 1.7;">
                    {!!$job->description!!}
                </div>
            </div>

            <!-- Botões de compartilhamento -->
            <div class="bg-light p-3 rounded-3 mb-4 border">
                <span class="fw-bold me-3">Partilhar:</span>
                <a class="btn btn-sm btn-outline-primary me-1" href="https://www.facebook.com/sharer/sharer.php?u={{ url('/vagas/'. $job->slug) }}" target="_blank"><i class="bi bi-facebook"></i></a>
                <a class="btn btn-sm btn-outline-primary me-1" href="https://www.linkedin.com/sharing/share-offsite/?url={{ url('/vagas/'. $job->slug) }}" target="_blank"><i class="bi bi-linkedin"></i></a>
                <a class="btn btn-sm btn-outline-success me-1" href="https://api.whatsapp.com/send?text={{ $job->title }} {{ url('/vagas/'. $job->slug) }}" target="_blank"><i class="bi bi-whatsapp"></i></a>
            </div>

            <!-- AD 1 -->
            <div class="my-4 text-center">
                <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2118765549976668" crossorigin="anonymous"></script>
                <ins class="adsbygoogle"
                     style="display:block"
                     data-ad-client="ca-pub-2118765549976668"
                     data-ad-slot="5838441610"
                     data-ad-format="auto"
                     data-full-width-responsive="true"></ins>
                <script>
                     (adsbygoogle = window.adsbygoogle || []).push({});
                </script>
            </div>
            
            <div id="apply-section" class="py-5 px-4 mb-4 text-center text-white rounded-3 shadow-lg position-relative overflow-hidden" style="background: linear-gradient(135deg, #2557a7 0%, #1e468a 100%);">
                <div class="position-relative z-1">
                    <div class="mb-3">
                        <i class="bi bi-lightning-charge-fill fs-1 text-warning"></i>
                    </div>
                    <h2 class="fw-bold mb-3">Candidaturas Automáticas</h2>
                    <p class="mb-4 fs-5 opacity-75">Deixe que nós façamos as candidaturas por você! Com base no seu CV, aplicamos automaticamente às vagas que combinam com o seu perfil.</p>
                    <a href="https://pay.kuenha.com/856ed35c-7b33-4e98-9352-954d22bc56a2" class="btn btn-warning btn-lg fw-bold rounded-pill px-5 text-dark shadow-sm hover-scale">
                        <i class="bi bi-eye me-2"></i>Ver Planos
                    </a>
                </div>
            </div>

            <!-- AD 2 -->
             <div class="my-4 text-center">
                 <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2118765549976668" crossorigin="anonymous"></script>
                <ins class="adsbygoogle"
                    style="display:block; text-align:center;"
                    data-ad-layout="in-article"
                    data-ad-format="fluid"
                    data-ad-client="ca-pub-2118765549976668"
                    data-ad-slot="7583808877">
                </ins>
                <script>
                    (adsbygoogle = window.adsbygoogle || []).push({});
                </script>
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
                        <h5 class="fw-bold m-0 text-dark">Vagas Recentes</h5>
                    </div>
                    <div class="list-group list-group-flush">
                      @foreach($LastJobs as $item)
                        <a href="{{ url('/vagas/' . $item->slug) }}" class="list-group-item list-group-item-action d-flex align-items-center py-3 border-0 border-bottom">
                            <i class="bi bi-briefcase text-primary me-3 bg-light p-2 rounded-circle"></i>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark text-truncate" style="max-width: 200px;">{{ $item->title }}</h6>
                                <small class="text-muted">{{ $item->company }}</small>
                            </div>
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
        .hover-scale:hover {
            transform: scale(1.05);
            transition: transform 0.2s ease;
        }
        .job-description h1, .job-description h2, .job-description h3 {
            font-weight: 700;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            font-size: 1.25rem;
        }
        .job-description ul {
            padding-left: 1.5rem;
            margin-bottom: 1.5rem;
        }
    </style>
@endsection('content')

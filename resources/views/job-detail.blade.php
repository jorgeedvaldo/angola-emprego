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
            <!-- Adição dos novos elementos -->
            <div class="vaga-meta mb-4">
                <!-- Botões de compartilhamento -->
                <div class="compartilhar-botoes mb-2">
                    <a class="btn btn-sm btn-outline-primary me-2" href="https://www.facebook.com/sharer/sharer.php?u={{ url('/vagas/'. $job->slug) }}&quote={{ $job->title }}%0A.%0ASe%20você%20deseja%20saber%20mais%20sobre%20esta%20oportunidade,%20por%20favor,%20clique%20no%20link:%20{{ url('/empregos/'. $job->slug) }}%0A."
                    target="_blank">
                        <i class="bi bi-facebook"></i> Facebook
                    </a>
                    <a class="btn btn-sm btn-outline-primary me-2" href="https://www.linkedin.com/sharing/share-offsite/?url={{ url('/vagas/'. $job->slug) }}&text={{ $job->title }}%0A.%0ASe%20você%20deseja%20saber%20mais%20sobre%20esta%20oportunidade,%20por%20favor,%20clique%20no%20link:%20{{ url('/empregos/'. $job->slug) }}%0A."
                    target="_blank">
                        <i class="bi bi-linkedin"></i> LinkedIn
                    </a>
                    <a class="btn btn-sm btn-outline-success me-2" href="https://api.whatsapp.com/send?text={{ $job->title }}%0A.%0ASe%20você%20deseja%20saber%20mais%20sobre%20esta%20oportunidade,%20por%20favor,%20clique%20no%20link:%20{{ url('/vagas/'. $job->slug) }}%0A."
                    target="_blank">
                        <i class="bi bi-whatsapp"></i> WhatsApp
                    </a>
                    <a class="btn btn-sm btn-outline-danger" href="mailto:?subject={{ $job->title }}&body=Confira esta oportunidade:%0A%0A{{ $job->title }}%0A%0APara mais detalhes, acesse: {{ url('/vagas/'. $job->slug) }}"
                    target="_blank">
                        <i class="bi bi-envelope"></i> Email
                    </a>
                </div>

                <!-- Informações da vaga -->
                <div class="vaga-info text-muted small">
                    <span class="me-3"><i class="bi bi-calendar me-1"></i> Publicada em: {{ date_format(new DateTime($job->created_at), 'd-m-Y') }}</span>
                    <span><i class="bi bi-building me-1"></i> Empresa: {{$job->company}}</span>
                </div>
            </div>
            <!-- Fim das adições -->

            <!-- AD 1 -->
		<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2118765549976668"
     crossorigin="anonymous"></script>
		<!-- AnuncioVizualizacao2 -->
		<ins class="adsbygoogle"
			 style="display:block"
			 data-ad-client="ca-pub-2118765549976668"
			 data-ad-slot="5838441610"
			 data-ad-format="auto"
			 data-full-width-responsive="true"></ins>
		<script>
			 (adsbygoogle = window.adsbygoogle || []).push({});
		</script>
		<!-- AD 2 -->
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

            <!-- Descricao -->
            {!!$job->description!!}

            <section class="py-5 px-4" style="background-color: #4a90e2; border-radius: 15px;">
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center mb-3"
                    style="width: 80px; height: 80px; background-color: rgba(255,255,255,0.2); border-radius: 50%;">
                <i class="fas fa-magic fa-2x text-white"></i>
                </div>
                <h2 class="text-white fw-bold mb-3">Candidaturas Automáticas</h2>
                <p class="text-white fs-5 mb-4 opacity-90">
                Deixe que nós façamos as candidaturas por você! Com base no seu CV, aplicamos automaticamente às vagas que combinam com o seu perfil.
                </p>
                <a href="https://pay.kuenha.com/856ed35c-7b33-4e98-9352-954d22bc56a2" class="btn btn-light btn-lg px-4 py-2 fw-bold" style="border-radius: 25px; color: #4a90e2;">
                <i class="fas fa-eye me-2"></i>Ver Planos
                </a>
            </div>

            <div class="row mt-5">
                <div class="col-md-6 mb-3">
                <div class="d-flex align-items-center">
                    <div class="me-3" style="width: 50px; height: 50px; background-color: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-clock text-white"></i>
                    </div>
                    <div>
                    <h5 class="text-white mb-1">Economia de Tempo</h5>
                    <p class="text-white mb-0 opacity-90">Candidature-se a múltiplas vagas automaticamente</p>
                    </div>
                </div>
                </div>

                <div class="col-md-6 mb-3">
                <div class="d-flex align-items-center">
                    <div class="me-3" style="width: 50px; height: 50px; background-color: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-target text-white"></i>
                    </div>
                    <div>
                    <h5 class="text-white mb-1">Precisão</h5>
                    <p class="text-white mb-0 opacity-90">Vagas selecionadas com base no seu perfil</p>
                    </div>
                </div>
                </div>

                <div class="col-md-6 mb-3">
                <div class="d-flex align-items-center">
                    <div class="me-3" style="width: 50px; height: 50px; background-color: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-chart-line text-white"></i>
                    </div>
                    <div>
                    <h5 class="text-white mb-1">Mais Oportunidades</h5>
                    <p class="text-white mb-0 opacity-90">Aumente suas chances de ser contratado</p>
                    </div>
                </div>
                </div>

                <div class="col-md-6 mb-3">
                <div class="d-flex align-items-center">
                    <div class="me-3" style="width: 50px; height: 50px; background-color: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-bell text-white"></i>
                    </div>
                    <div>
                    <h5 class="text-white mb-1">Notificações</h5>
                    <p class="text-white mb-0 opacity-90">Receba atualizações sobre suas candidaturas</p>
                    </div>
                </div>
                </div>
            </div>
            </section>
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

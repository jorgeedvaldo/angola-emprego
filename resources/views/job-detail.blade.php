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

            <section class="py-5" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-8 col-md-7">
        <div class="text-white">
          <h2 class="fw-bold mb-3" style="font-size: 2.2rem;">
            <i class="fas fa-robot me-3" style="color: #60a5fa;"></i>
            Candidaturas Automáticas
          </h2>
          <p class="lead mb-4" style="font-size: 1.1rem; line-height: 1.6;">
            Deixe que nós façamos as candidaturas por você! Com base no seu CV, aplicamos
            automaticamente às vagas que combinam com o seu perfil.
          </p>
          <div class="d-flex align-items-center">
            <div class="me-4">
              <i class="fas fa-check-circle text-success me-2"></i>
              <span>Candidaturas 24/7</span>
            </div>
            <div class="me-4">
              <i class="fas fa-check-circle text-success me-2"></i>
              <span>Match Inteligente</span>
            </div>
            <div>
              <i class="fas fa-check-circle text-success me-2"></i>
              <span>Sem Esforço Manual</span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4 col-md-5 text-center">
        <div class="mt-4 mt-md-0">
          <a href="#planos" class="btn btn-light btn-lg px-4 py-3 fw-bold"
             style="border-radius: 50px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); transition: all 0.3s ease;">
            <i class="fas fa-eye me-2"></i>
            Ver Planos
          </a>
          <div class="mt-3">
            <small class="text-white-50">
              <i class="fas fa-users me-1"></i>
              Mais de 1000+ profissionais já usam
            </small>
          </div>
        </div>
      </div>
    </div>

     Seção de benefícios adicionais
    <div class="row mt-5">
      <div class="col-12">
        <div class="row text-center text-white">
          <div class="col-md-4 mb-3">
            <div class="p-3">
              <i class="fas fa-clock fa-2x mb-3" style="color: #60a5fa;"></i>
              <h5>Economia de Tempo</h5>
              <p class="small">Poupe horas de candidaturas manuais</p>
            </div>
          </div>
          <div class="col-md-4 mb-3">
            <div class="p-3">
              <i class="fas fa-target fa-2x mb-3" style="color: #60a5fa;"></i>
              <h5>Precisão no Match</h5>
              <p class="small">IA analisa seu perfil e encontra vagas ideais</p>
            </div>
          </div>
          <div class="col-md-4 mb-3">
            <div class="p-3">
              <i class="fas fa-chart-line fa-2x mb-3" style="color: #60a5fa;"></i>
              <h5>Mais Oportunidades</h5>
              <p class="small">Nunca perca uma vaga que combine com você</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
.btn-light:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.3) !important;
}

@media (max-width: 768px) {
  .lead {
    font-size: 1rem !important;
  }

  h2 {
    font-size: 1.8rem !important;
  }

  .d-flex.align-items-center {
    flex-direction: column;
    align-items: flex-start !important;
  }

  .d-flex.align-items-center > div {
    margin-bottom: 0.5rem;
    margin-right: 0 !important;
  }
}
</style>
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

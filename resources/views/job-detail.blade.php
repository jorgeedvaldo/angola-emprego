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

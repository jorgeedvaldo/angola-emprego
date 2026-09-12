@extends('templates.app')
@section('title', __('site.sobre.meta_titulo'))
@section('description', __('site.sobre.meta_descricao'))
@section('canonical_link', url('/sobre'))

@section('head-scripts')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [{
    "@type": "Question",
    "name": @json(__('site.sobre.faq1_pergunta')),
    "acceptedAnswer": {
      "@type": "Answer",
      "text": @json(__('site.sobre.faq1_resposta'))
    }
  },{
    "@type": "Question",
    "name": @json(__('site.sobre.faq2_pergunta')),
    "acceptedAnswer": {
      "@type": "Answer",
      "text": @json(__('site.sobre.faq2_resposta'))
    }
  },{
    "@type": "Question",
    "name": @json(__('site.sobre.faq3_pergunta')),
    "acceptedAnswer": {
      "@type": "Answer",
      "text": @json(__('site.sobre.faq3_resposta'))
    }
  }]
}
</script>
@endsection

@section('content')
    <!-- Page Header -->
    <div class="bg-light py-5">
      <div class="container text-center">
         <h1 class="fw-bold mb-3 text-dark">{{ __('site.sobre.titulo') }}</h1>
         <p class="text-muted mx-auto" style="max-width: 600px;">{{ __('site.sobre.intro') }}</p>
      </div>
    </div>

    <!-- About Section -->
    <section class="section py-5">
      <div class="container">
        <div class="row align-items-center gy-5">
          <div class="col-lg-6">
             <div class="pe-lg-5">
                <span class="text-primary fw-bold text-uppercase small">{{ __('site.sobre.historia') }}</span>
                <h2 class="fw-bold text-dark mt-2 mb-4">{{ __('site.sobre.historia_titulo') }}</h2>
                <p class="text-muted lead mb-4">
                    {{ __('site.sobre.historia_subtitulo') }}
                </p>
                <p class="text-muted mb-4">
                    {{ __('site.sobre.historia_texto') }}
                </p>
                
                <h4 class="fw-bold text-dark mb-3">{{ __('site.sobre.missao') }}</h4>
                <p class="text-muted mb-4">
                    {{ __('site.sobre.missao_texto') }}
                </p>
             </div>
          </div>
          <div class="col-lg-6">
            <div class="position-relative">
                <img src="{{asset('assets/img/about.jpg')}}" alt="About" class="img-fluid rounded-3 shadow-lg w-100">
                <div class="position-absolute bottom-0 start-0 p-4 bg-primary text-white rounded-end m-4 d-none d-lg-block" style="max-width: 300px;">
                    <p class="mb-0 fw-bold">{{ __('site.sobre.citacao') }}</p>
                </div>
            </div>
          </div>
        </div>
        
        <div class="row mt-5 pt-5">
            <div class="col-12 text-center mb-5">
                <h2 class="fw-bold">{{ __('site.sobre.oferecemos') }}</h2>
            </div>
            <div class="col-md-4 mb-4">
                <div class="p-4 border rounded-3 h-100 bg-light">
                    <i class="bi bi-briefcase text-primary fs-1 mb-3"></i>
                    <h5 class="fw-bold">{{ __('site.sobre.vagas_titulo') }}</h5>
                    <p class="text-muted small">{{ __('site.sobre.vagas_texto') }}</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="p-4 border rounded-3 h-100 bg-light">
                    <i class="bi bi-search text-primary fs-1 mb-3"></i>
                    <h5 class="fw-bold">{{ __('site.sobre.busca_titulo') }}</h5>
                    <p class="text-muted small">{{ __('site.sobre.busca_texto') }}</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="p-4 border rounded-3 h-100 bg-light">
                    <i class="bi bi-lightbulb text-primary fs-1 mb-3"></i>
                    <h5 class="fw-bold">{{ __('site.sobre.dicas_titulo') }}</h5>
                    <p class="text-muted small">{{ __('site.sobre.dicas_texto') }}</p>
                </div>
            </div>
        </div>
      </div>
    </section>

    <!-- FAQ Section -->
    <section class="section py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold text-dark">{{ __('site.sobre.faq') }}</h2>
                <p class="text-muted">{{ __('site.sobre.faq_intro') }}</p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion accordion-flush" id="faqAccordion">
                        <div class="accordion-item border mb-3 rounded shadow-sm overflow-hidden">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    {{ __('site.sobre.faq1_pergunta') }}
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">{{ __('site.sobre.faq1_resposta') }}</div>
                            </div>
                        </div>
                        
                        <div class="accordion-item border mb-3 rounded shadow-sm overflow-hidden">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    {{ __('site.sobre.faq2_pergunta') }}
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">{{ __('site.sobre.faq2_resposta') }}</div>
                            </div>
                        </div>
                        
                         <div class="accordion-item border mb-3 rounded shadow-sm overflow-hidden">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    {{ __('site.sobre.faq3_pergunta') }}
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">{{ __('site.sobre.faq3_resposta') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact section py-5">
        <div class="container">
           <div class="row justify-content-center mb-5">
             <div class="col-lg-8 text-center">
                <h2 class="fw-bold">{{ __('site.sobre.contactos') }}</h2>
                <p class="text-muted">{{ __('site.sobre.contactos_intro') }}</p>
             </div>
           </div>
           
           <div class="row gy-4 justify-content-center">
             <div class="col-lg-4">
                 <div class="text-center p-4 border rounded-3 h-100 shadow-sm">
                     <i class="bi bi-envelope fs-2 text-primary mb-3 d-block"></i>
                     <h4 class="fw-bold">{{ __('site.sobre.email') }}</h4>
                     <p class="text-muted">geral@angolaemprego.com</p>
                 </div>
             </div>
             <div class="col-lg-4">
                 <div class="text-center p-4 border rounded-3 h-100 shadow-sm">
                     <i class="bi bi-geo-alt fs-2 text-primary mb-3 d-block"></i>
                     <h4 class="fw-bold">{{ __('site.sobre.endereco') }}</h4>
                     <p class="text-muted">{{ __('site.sobre.morada') }}</p>
                 </div>
             </div>
           </div>
           
        </div>
    </section>
@endsection('content')
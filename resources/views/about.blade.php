@extends('templates.app')
@section('title', 'Sobre')
@section('description', 'Angola Emprego é o maior portal de emprego em Angola, comprometido em ajudar milhares de angolanos a encontrar as melhores oportunidades de trabalho diariamente')
@section('canonical_link', url('/sobre'))

@section('content')
    <!-- Page Header -->
    <div class="bg-light py-5">
      <div class="container text-center">
         <h1 class="fw-bold mb-3 text-dark">Sobre Nós</h1>
         <p class="text-muted mx-auto" style="max-width: 600px;">Conheça a nossa história, missão e o que nos move a conectar milhares de profissionais às melhores oportunidades em Angola.</p>
      </div>
    </div>

    <!-- About Section -->
    <section class="section py-5">
      <div class="container">
        <div class="row align-items-center gy-5">
          <div class="col-lg-6">
             <div class="pe-lg-5">
                <span class="text-primary fw-bold text-uppercase small">Nossa História</span>
                <h2 class="fw-bold text-dark mt-2 mb-4">O Maior portal de emprego em Angola</h2>
                <p class="text-muted lead mb-4">
                    Criado em 2019, o nosso portal tem ajudado muita gente a sair do desemprego.
                </p>
                <p class="text-muted mb-4">
                    Angola Emprego é o maior portal de emprego em Angola, comprometido em ajudar milhares de angolanos a encontrar as melhores oportunidades de trabalho diariamente. Nossa missão é ser a ponte entre os candidatos em busca de emprego e as empresas que desejam contratar os melhores talentos.
                </p>
                
                <h4 class="fw-bold text-dark mb-3">Nossa Missão</h4>
                <p class="text-muted mb-4">
                    Nosso objetivo é fornecer uma plataforma abrangente e fácil de usar, onde os candidatos possam acessar as melhores vagas de emprego disponíveis no mercado angolano. Estamos dedicados a ajudar os angolanos a alcançar seus objetivos profissionais e a construir carreiras de sucesso.
                </p>
             </div>
          </div>
          <div class="col-lg-6">
            <div class="position-relative">
                <img src="{{asset('assets/img/about.jpg')}}" alt="About" class="img-fluid rounded-3 shadow-lg w-100">
                <div class="position-absolute bottom-0 start-0 p-4 bg-primary text-white rounded-end m-4 d-none d-lg-block" style="max-width: 300px;">
                    <p class="mb-0 fw-bold">"Conectando sonhos a oportunidades reais em todo o país."</p>
                </div>
            </div>
          </div>
        </div>
        
        <div class="row mt-5 pt-5">
            <div class="col-12 text-center mb-5">
                <h2 class="fw-bold">O Que Oferecemos</h2>
            </div>
            <div class="col-md-4 mb-4">
                <div class="p-4 border rounded-3 h-100 bg-light">
                    <i class="bi bi-briefcase text-primary fs-1 mb-3"></i>
                    <h5 class="fw-bold">Vagas Atualizadas</h5>
                    <p class="text-muted small">Mantemos nosso portal atualizado com as mais recentes ofertas de emprego em diversas áreas e setores.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="p-4 border rounded-3 h-100 bg-light">
                    <i class="bi bi-search text-primary fs-1 mb-3"></i>
                    <h5 class="fw-bold">Busca Avançada</h5>
                    <p class="text-muted small">Filtre as vagas por localização, setor e nível de experiência para encontrar a vaga ideal.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="p-4 border rounded-3 h-100 bg-light">
                    <i class="bi bi-lightbulb text-primary fs-1 mb-3"></i>
                    <h5 class="fw-bold">Dicas de Carreira</h5>
                    <p class="text-muted small">Recursos, conselhos sobre entrevistas e orientações para elaboração de currículos eficazes.</p>
                </div>
            </div>
        </div>
      </div>
    </section>

    <!-- FAQ Section -->
    <section class="section py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold text-dark">Perguntas Frequentes</h2>
                <p class="text-muted">Tire suas dúvidas sobre o funcionamento do portal.</p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion accordion-flush" id="faqAccordion">
                        <div class="accordion-item border mb-3 rounded shadow-sm overflow-hidden">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    O que é o Angola Emprego?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">O Angola Emprego é um site dedicado a anúncios de vagas de emprego em Angola. Nosso objetivo é conectar candidatos a oportunidades de trabalho e ajudar empresas a divulgar suas vagas de forma gratuita.</div>
                            </div>
                        </div>
                        
                        <div class="accordion-item border mb-3 rounded shadow-sm overflow-hidden">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    O Angola Emprego cobra para divulgar vagas?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">Não, o Angola Emprego não cobra para dar emprego ou para publicar vagas. Nosso serviço é totalmente gratuito.</div>
                            </div>
                        </div>
                        
                         <div class="accordion-item border mb-3 rounded shadow-sm overflow-hidden">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    As vagas publicadas pertencem ao Angola Emprego?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">Não, somos apenas uma plataforma que divulga oportunidades oferecidas por empresas e recrutadores terceiros.</div>
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
                <h2 class="fw-bold">Contactos</h2>
                <p class="text-muted">Estamos aqui para ajudar. Entre em contacto connosco.</p>
             </div>
           </div>
           
           <div class="row gy-4 justify-content-center">
             <div class="col-lg-4">
                 <div class="text-center p-4 border rounded-3 h-100 shadow-sm">
                     <i class="bi bi-envelope fs-2 text-primary mb-3 d-block"></i>
                     <h4 class="fw-bold">Email</h4>
                     <p class="text-muted">geral@angolaemprego.com</p>
                 </div>
             </div>
             <div class="col-lg-4">
                 <div class="text-center p-4 border rounded-3 h-100 shadow-sm">
                     <i class="bi bi-geo-alt fs-2 text-primary mb-3 d-block"></i>
                     <h4 class="fw-bold">Endereço</h4>
                     <p class="text-muted">Luanda, Maianga, Rua Nkwame Nkrumah</p>
                 </div>
             </div>
           </div>
           
        </div>
    </section>
@endsection('content')
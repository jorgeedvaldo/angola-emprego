@extends('templates.app')
@section('title', 'Sobre')
@section('description', 'Angola Emprego é o maior portal de emprego em Angola, comprometido em ajudar milhares de angolanos a encontrar as melhores oportunidades de trabalho diariamente')
@section('canonical_link', url('/sobre'))

@section('content')
    <!-- Page Title -->
    <div class="page-title" data-aos="fade">
      <div class="container d-lg-flex justify-content-between align-items-center">
        <h1 class="mb-2 mb-lg-0">Sobre Nós</h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="{{url('/')}}">Início</a></li>
            <li class="current">Sobre</li>
          </ol>
        </nav>
      </div>
    </div><!-- End Page Title -->

    <!-- Service Details Section -->
    <section id="service-details" class="service-details section">

      <div class="container">

        <div class="row gy-4">

          <div class="col-lg-8" data-aos="fade-up" data-aos-delay="200">
            <h1>O Maior portal de emprego em Angola</h1>
            <p>Criado em 2019, o nosso portal tem ajudado muita gente a sair do desemprego<br />Angola Emprego &eacute; o maior portal de emprego em Angola, comprometido em ajudar milhares de angolanos a encontrar as melhores oportunidades de trabalho diariamente. Nossa miss&atilde;o &eacute; ser a ponte entre os candidatos em busca de emprego e as empresas que desejam contratar os melhores talentos.</p>
            <h3>Nossa Miss&atilde;o</h3>
            <p>Nosso objetivo &eacute; fornecer uma plataforma abrangente e f&aacute;cil de usar, onde os candidatos possam acessar as melhores vagas de emprego dispon&iacute;veis no mercado angolano. Estamos dedicados a ajudar os angolanos a alcan&ccedil;ar seus objetivos profissionais e a construir carreiras de sucesso.</p>
            <h3>O Que Oferecemos</h3>
            <ol>
            <li><strong>Vagas de Emprego Atualizadas Diariamente:</strong> Mantemos nosso portal atualizado com as mais recentes ofertas de emprego em diversas &aacute;reas e setores, garantindo que voc&ecirc; tenha acesso &agrave;s melhores oportunidades dispon&iacute;veis.</li>
            <li><strong>Ferramentas de Busca Avan&ccedil;ada:</strong> Nossas ferramentas de busca permitem que voc&ecirc; filtre as vagas por localiza&ccedil;&atilde;o, setor, n&iacute;vel de experi&ecirc;ncia e outros crit&eacute;rios importantes, facilitando a busca pela vaga ideal.</li>
            <li><strong>Recursos e Dicas de Carreira</strong>: Al&eacute;m de listar vagas de emprego, oferecemos uma ampla gama de recursos, incluindo dicas de carreira, conselhos sobre como se destacar em entrevistas, e orienta&ccedil;&otilde;es para a elabora&ccedil;&atilde;o de curr&iacute;culos eficazes.</li>
            <li><strong>Cadastro de Curr&iacute;culos</strong>: Os candidatos podem cadastrar seus curr&iacute;culos em nosso banco de dados, permitindo que empregadores em potencial os encontrem e entrem em contato diretamente.</li>
            <li><strong>An&aacute;lise de Mercado de Trabalho:</strong> Fornecemos an&aacute;lises detalhadas sobre o mercado de trabalho angolano, ajudando candidatos e empresas a entenderem as tend&ecirc;ncias e a tomarem decis&otilde;es informadas.</li>
            </ol>
          </div>

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
            <img src="{{asset('assets/img/about.jpg')}}" alt="" class="img-fluid services-img">

          </div>

        </div>

      </div>

    </section><!-- /Service Details Section -->


    <!-- Faq Section -->
  <section id="faq" class="faq section light-background">

    <!-- Section Title -->
    <div class="container section-title" data-aos="fade-up">
      <h2>F.A.Q</h2>
      <p><span>Questões</span> <span class="description-title">Frequentes</span></p>
    </div><!-- End Section Title -->

    <div class="container">

      <div class="row justify-content-center">

        <div class="col-lg-10" data-aos="fade-up" data-aos-delay="100">

          <div class="faq-container">

            <div class="faq-item faq-active">
              <h3>O que é o Angola Emprego?</h3>
              <div class="faq-content">
                <p>O Angola Emprego é um site dedicado a anúncios de vagas de emprego em Angola. Nosso objetivo é conectar candidatos a oportunidades de trabalho e ajudar empresas a divulgar suas vagas de forma gratuita.</p>
              </div>
              <i class="faq-toggle bi bi-chevron-right"></i>
            </div><!-- End Faq item-->

            <div class="faq-item">
              <h3>O Angola Emprego cobra para divulgar vagas ou encontrar emprego?</h3>
              <div class="faq-content">
                <p>Não, o Angola Emprego não cobra para dar emprego ou para publicar vagas. Nosso serviço é totalmente gratuito, tanto para candidatos quanto para empresas que desejam anunciar oportunidades.</p>
              </div>
              <i class="faq-toggle bi bi-chevron-right"></i>
            </div><!-- End Faq item-->

            <div class="faq-item">
              <h3>As vagas publicadas pertencem ao Angola Emprego?</h3>
              <div class="faq-content">
                <p>Não, as vagas anunciadas no Angola Emprego não pertencem a nós. Somos apenas uma plataforma que divulga oportunidades oferecidas por empresas e recrutadores em Angola.</p>
              </div>
              <i class="faq-toggle bi bi-chevron-right"></i>
            </div><!-- End Faq item-->

            <div class="faq-item">
              <h3>Quais serviços adicionais o Angola Emprego oferece?</h3>
              <div class="faq-content">
                <p>Além de vagas de emprego, oferecemos artigos e notícias sobre o mercado de trabalho, modelos de CVs, informações sobre bolsas de estudo e a opção de candidatura espontânea para os usuários.</p>
              </div>
              <i class="faq-toggle bi bi-chevron-right"></i>
            </div><!-- End Faq item-->

            <div class="faq-item">
              <h3>Como posso entrar em contato com o Angola Emprego?</h3>
              <div class="faq-content">
                <p>Você pode nos contatar através da seção "Contactos" no site. Estamos disponíveis para responder dúvidas e receber feedback sobre nossos serviços.</p>
              </div>
              <i class="faq-toggle bi bi-chevron-right"></i>
            </div><!-- End Faq item-->

            <div class="faq-item">
              <h3>Onde posso encontrar os termos e condições do site?</h3>
              <div class="faq-content">
                <p>Os Termos, Condições e Privacidade estão disponíveis no rodapé do site, na seção específica. Lá você encontra todas as informações sobre o uso da plataforma.</p>
              </div>
              <i class="faq-toggle bi bi-chevron-right"></i>
            </div><!-- End Faq item-->

          </div>

        </div><!-- End Faq Column-->

      </div>

    </div>

  </section><!-- /Faq Section -->

    <!-- Contact Section -->
    <section id="contact" class="contact section">

        <!-- Section Title -->
        <div class="container section-title" data-aos="fade-up">
          <h2>Contactos</h2>
          <p><span>Precisa de Ajuda?</span> <span class="description-title">Entre em Contacto</span></p>
        </div><!-- End Section Title -->

        <div class="container" data-aos="fade-up" data-aos-delay="100">

          <div class="row gy-4">

            <div class="col-lg-5">

              <div class="info-wrap">
                <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="200">
                  <i class="bi bi-geo-alt flex-shrink-0"></i>
                  <div>
                    <h3>Endereço</h3>
                    <p>Luanda, Maianga, Rua Nkwame Nkrumah</p>
                  </div>
                </div><!-- End Info Item -->

                <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="300">
                  <i class="bi bi-telephone flex-shrink-0"></i>
                  <div>
                    <h3>Call Us</h3>
                    <p>+1 5589 55488 55</p>
                  </div>
                </div><!-- End Info Item -->

                <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="400">
                  <i class="bi bi-envelope flex-shrink-0"></i>
                  <div>
                    <h3>Nosso e-mail</h3>
                    <p>geral@angolaemprego.com</p>
                  </div>
                </div><!-- End Info Item -->

                <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d48389.78314118045!2d-74.006138!3d40.710059!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c25a22a3bda30d%3A0xb89d1fe6bc499443!2sDowntown%20Conference%20Center!5e0!3m2!1sen!2sus!4v1676961268712!5m2!1sen!2sus" frameborder="0" style="border:0; width: 100%; height: 270px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
              </div>
            </div>

            <div class="col-lg-7">
              <form action="forms/contact.php" method="post" class="php-email-form" data-aos="fade-up" data-aos-delay="200">
                <div class="row gy-4">

                  <div class="col-md-6">
                    <label for="name-field" class="pb-2">Seu nome</label>
                    <input type="text" name="name" id="name-field" class="form-control" required="">
                  </div>

                  <div class="col-md-6">
                    <label for="email-field" class="pb-2">Seu e-mail</label>
                    <input type="email" class="form-control" name="email" id="email-field" required="">
                  </div>

                  <div class="col-md-12">
                    <label for="subject-field" class="pb-2">Assunto</label>
                    <input type="text" class="form-control" name="subject" id="subject-field" required="">
                  </div>

                  <div class="col-md-12">
                    <label for="message-field" class="pb-2">Mensagem</label>
                    <textarea class="form-control" name="message" rows="10" id="message-field" required=""></textarea>
                  </div>

                  <div class="col-md-12 text-center">
                    <div class="loading">Enviando</div>
                    <div class="error-message"></div>
                    <div class="sent-message">Sua mensagem foi enviada. Obrigado!</div>

                    <button type="submit">Enviar</button>
                  </div>

                </div>
              </form>
            </div><!-- End Contact Form -->

          </div>

        </div>

      </section><!-- /Contact Section -->
@endsection('content')
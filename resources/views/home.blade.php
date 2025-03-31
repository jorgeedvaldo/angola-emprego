@extends('templates.app')
@section('title', 'Início')
@section('description', 'Angola Emprego é o maior portal de emprego em Angola, comprometido em ajudar milhares de angolanos a encontrar as melhores oportunidades de trabalho diariamente')
@section('canonical_link', url('/'))

@section('head-scripts')

@endsection

@section('content')
    <!-- Hero Section -->
    <section id="hero" class="hero section light-background">

        <div class="container">
            <div class="row gy-4">
            <div class="col-lg-6 order-2 order-lg-1 d-flex flex-column justify-content-center" data-aos="zoom-out">
                <h1>Bem-vindo ao <span>AngolaEmprego</span></h1>
                <p>O Maior portal de emprego em Angola</p>
                <div class="d-flex">
                <a href="{{url('/vagas')}}" class="btn-get-started">Ver Oportunidades</a>
                </div>
            </div>
            </div>
        </div>

    </section><!-- /Hero Section -->

    <!-- Job Section -->
    <section id="about" class="about section light-background">

        <!-- Section Title -->
        <div class="container section-title" data-aos="fade-up">
          <h2>Empregos</h2>
          <p><span>Encontre mais oportunidades de</span> <span class="description-title">emprego</span></p>
        </div><!-- End Section Title -->

        <div class="container">

          <div class="row gy-3">

            @foreach($jobs as $job)
              <!-- Primeiro Card -->
              <div class="col-md-6 mb-4">
                  <div class="card h-100">
                      <div class="card-header bg-primary text-white">
                          <h5 class="card-title mb-0">{{ $job->title }}</h5>
                      </div>
                      <div class="card-body">
                          <h6 class="card-subtitle mb-2 text-muted">{{ $job->company }}</h6>
                          <p class="card-text">
                            {!! \Illuminate\Support\Str::limit(strip_tags($job->description), 207, $end='...') !!}
                          </p>
                          <ul class="list-unstyled">
                              <li><strong>Local:</strong> {{ $job->location }}</li>
                          </ul>
                          <a href="{{ url('/vagas/' . $job->slug) }}" class="btn btn-primary">Ver Detalhes</a>
                      </div>
                      <div class="card-footer text-muted">
                          Publicado em {{ date_format(new DateTime($job->created_at), 'd-m-Y') }}
                      </div>
                  </div>
              </div>
            @endforeach
          </div>

        </div>

      </section><!-- /Job Section -->

      <!-- /Blog Posts Section -->
      <section id="blog-posts" class="blog-posts section">

          <!-- Section Title -->
          <div class="container section-title" data-aos="fade-up">
              <h2>Blog</h2>
              <p><span>Últimas novidades do nosso</span> <span class="description-title">Blog</span></p>
          </div><!-- End Section Title -->

          <div class="container">
            <div class="row gy-4">

              @foreach($posts as $post)
              <div class="col-lg-4">
                <article>

                  <div class="post-img">
                    <img src="assets/img/blog/blog-1.jpg" alt="" class="img-fluid">
                  </div>

                  <p class="post-category">Politics</p>

                  <h3 class="title">
                    <a href="{{ url('/' . $post->slug) }}">{{ $post->title }}</a>
                  </h3>

                  <div class="d-flex align-items-center">
                    <img src="assets/img/blog/blog-author.jpg" alt="" class="img-fluid post-author-img flex-shrink-0">
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

        </section><!-- /Blog Posts Section -->


      <!-- Team Section -->
      <section id="team" class="team section light-background">

        <!-- Section Title -->
        <div class="container section-title" data-aos="fade-up">
          <h2>Team</h2>
          <p><span>Our Hardworking</span> <span class="description-title">Team</span></p>
        </div><!-- End Section Title -->

        <div class="container">

          <div class="row gy-4">

            <div class="col-lg-3 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="100">
              <div class="team-member">
                <div class="member-img">
                  <img src="assets/img/team/team-1.jpg" class="img-fluid" alt="">
                  <div class="social">
                    <a href=""><i class="bi bi-twitter-x"></i></a>
                    <a href=""><i class="bi bi-facebook"></i></a>
                    <a href=""><i class="bi bi-instagram"></i></a>
                    <a href=""><i class="bi bi-linkedin"></i></a>
                  </div>
                </div>
                <div class="member-info">
                  <h4>Walter White</h4>
                  <span>Chief Executive Officer</span>
                </div>
              </div>
            </div><!-- End Team Member -->

            <div class="col-lg-3 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="200">
              <div class="team-member">
                <div class="member-img">
                  <img src="assets/img/team/team-2.jpg" class="img-fluid" alt="">
                  <div class="social">
                    <a href=""><i class="bi bi-twitter-x"></i></a>
                    <a href=""><i class="bi bi-facebook"></i></a>
                    <a href=""><i class="bi bi-instagram"></i></a>
                    <a href=""><i class="bi bi-linkedin"></i></a>
                  </div>
                </div>
                <div class="member-info">
                  <h4>Sarah Jhonson</h4>
                  <span>Product Manager</span>
                </div>
              </div>
            </div><!-- End Team Member -->

            <div class="col-lg-3 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="300">
              <div class="team-member">
                <div class="member-img">
                  <img src="assets/img/team/team-3.jpg" class="img-fluid" alt="">
                  <div class="social">
                    <a href=""><i class="bi bi-twitter-x"></i></a>
                    <a href=""><i class="bi bi-facebook"></i></a>
                    <a href=""><i class="bi bi-instagram"></i></a>
                    <a href=""><i class="bi bi-linkedin"></i></a>
                  </div>
                </div>
                <div class="member-info">
                  <h4>William Anderson</h4>
                  <span>CTO</span>
                </div>
              </div>
            </div><!-- End Team Member -->

            <div class="col-lg-3 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="400">
              <div class="team-member">
                <div class="member-img">
                  <img src="assets/img/team/team-4.jpg" class="img-fluid" alt="">
                  <div class="social">
                    <a href=""><i class="bi bi-twitter-x"></i></a>
                    <a href=""><i class="bi bi-facebook"></i></a>
                    <a href=""><i class="bi bi-instagram"></i></a>
                    <a href=""><i class="bi bi-linkedin"></i></a>
                  </div>
                </div>
                <div class="member-info">
                  <h4>Amanda Jepson</h4>
                  <span>Accountant</span>
                </div>
              </div>
            </div><!-- End Team Member -->

          </div>

        </div>

      </section><!-- /Team Section -->

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
          <h2>Contact</h2>
          <p><span>Need Help?</span> <span class="description-title">Contact Us</span></p>
        </div><!-- End Section Title -->

        <div class="container" data-aos="fade-up" data-aos-delay="100">

          <div class="row gy-4">

            <div class="col-lg-5">

              <div class="info-wrap">
                <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="200">
                  <i class="bi bi-geo-alt flex-shrink-0"></i>
                  <div>
                    <h3>Address</h3>
                    <p>A108 Adam Street, New York, NY 535022</p>
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
                    <h3>Email Us</h3>
                    <p>info@example.com</p>
                  </div>
                </div><!-- End Info Item -->

                <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d48389.78314118045!2d-74.006138!3d40.710059!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c25a22a3bda30d%3A0xb89d1fe6bc499443!2sDowntown%20Conference%20Center!5e0!3m2!1sen!2sus!4v1676961268712!5m2!1sen!2sus" frameborder="0" style="border:0; width: 100%; height: 270px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
              </div>
            </div>

            <div class="col-lg-7">
              <form action="forms/contact.php" method="post" class="php-email-form" data-aos="fade-up" data-aos-delay="200">
                <div class="row gy-4">

                  <div class="col-md-6">
                    <label for="name-field" class="pb-2">Your Name</label>
                    <input type="text" name="name" id="name-field" class="form-control" required="">
                  </div>

                  <div class="col-md-6">
                    <label for="email-field" class="pb-2">Your Email</label>
                    <input type="email" class="form-control" name="email" id="email-field" required="">
                  </div>

                  <div class="col-md-12">
                    <label for="subject-field" class="pb-2">Subject</label>
                    <input type="text" class="form-control" name="subject" id="subject-field" required="">
                  </div>

                  <div class="col-md-12">
                    <label for="message-field" class="pb-2">Message</label>
                    <textarea class="form-control" name="message" rows="10" id="message-field" required=""></textarea>
                  </div>

                  <div class="col-md-12 text-center">
                    <div class="loading">Loading</div>
                    <div class="error-message"></div>
                    <div class="sent-message">Your message has been sent. Thank you!</div>

                    <button type="submit">Send Message</button>
                  </div>

                </div>
              </form>
            </div><!-- End Contact Form -->

          </div>

        </div>

      </section><!-- /Contact Section -->
@endsection('content')

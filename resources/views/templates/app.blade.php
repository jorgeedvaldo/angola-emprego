<!DOCTYPE html>
<html lang="pt">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>@yield('title') - {{env('APP_NAME')}}</title>
  <meta name="description" content="@yield('description')" />
  <meta name="robots" content="follow, index, max-snippet:-1, max-video-preview:-1, max-image-preview:large"/>

  <!-- Favicons -->
  <link rel="icon" href="{{ asset('assets/img/favicon.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('assets/img/apple-touch-icon.png') }}">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- Vendor CSS Files -->
  <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/aos/aos.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet">

  <!-- Google Analytcs tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-VW5BKCFM0R"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-VW5BKCFM0R');
  </script>

  <!-- AdSense -->
  <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2118765549976668" crossorigin="anonymous"></script>

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="article" />
  <meta property="og:title" content="@yield('title') - {{env('APP_NAME')}}" />
  <meta property="og:url" content="@yield('canonical_link')" />
  <meta property="og:description" content="@yield('description')" />
  <meta property="article:published_time" content="@yield('created_at')" />
  <meta property="article:modified_time" content="@yield('updated_at')" />
  <meta property="og:site_name" content="Angola Emprego" />
  <meta property="og:image" content="@yield('url')" />
  <meta property="og:image:width" content="1200" />
  <meta property="og:image:height" content="700" />
  <meta property="og:image:alt" content />
  <meta property="og:locale" content="pt_PT" />
  <meta name="author" content="Edivaldo" />
  <meta name="twitter:text:title" content="@yield('title') - {{env('APP_NAME')}}" />
  <meta name="twitter:image" content="@yield('url')" />
  <meta name="twitter:card" content="summary_large_image" />

  @yield('head-scripts')
  <style>
    :root {
      --primary-color: #2557a7; /* Indeed Blue */
      --text-color: #2d2d2d;
      --bg-light: #f3f2f1;
    }
    body {
      font-family: 'Open Sans', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      color: var(--text-color);
      background-color: #fff;
    }
    .header {
      background: #fff;
      border-bottom: 1px solid #e4e2e0;
      padding: 15px 0;
      box-shadow: none; /* Remove heavy shadow */
    }
    .header .logo img {
      max-height: 40px;
    }
    .navmenu ul li a {
      color: var(--text-color);
      font-weight: 600;
      font-size: 15px;
      padding: 10px 15px;
    }
    .navmenu ul li a:hover, .navmenu ul li a.active {
      color: var(--primary-color);
    }
    .btn-get-started {
      background: var(--primary-color);
      color: #fff !important;
      border-radius: 8px;
      padding: 10px 20px !important;
      font-weight: 700;
      border: 1px solid var(--primary-color);
    }
    .btn-get-started:hover {
      background: #1e468a;
    }
    .footer {
      background: #f3f2f1;
      border-top: 1px solid #e4e2e0;
      color: #595959;
      padding-top: 50px;
    }
    .footer h4 {
      color: #2d2d2d;
      font-size: 16px;
      font-weight: 700;
      margin-bottom: 20px;
    }
    .footer .sitename {
      color: #2d2d2d;
      font-size: 24px;
      font-weight: 700;
    }
    .main {
      min-height: 80vh;
    }
  </style>
</head>

<body class="index-page">

  <header id="header" class="header sticky-top">

    <div class="branding d-flex align-items-center">

      <div class="container position-relative d-flex align-items-center justify-content-between">
        <a href="{{url('/')}}" class="logo d-flex align-items-center text-decoration-none">
          <!-- Uncomment the line below if you also wish to use an image logo -->
          <img src="{{asset('assets/img/logo.svg')}}" alt="Angola Emprego" style="height: 40px;">
          <!-- <h1 class="sitename">AngolaEmprego</h1> -->
        </a>

        <nav id="navmenu" class="navmenu">
          <ul>
            <li><a href="{{url('/')}}">Início</a></li>
            <li><a href="{{url('/vagas')}}">Empregos</a></li>
            <li><a href="{{route('courses.index')}}">Cursos</a></li>
            <li><a href="{{url('/blog')}}">Blog</a></li>
            <li><a href="{{url('/atm-com-dinheiro')}}">ATMs</a></li>
            
            @guest
                <li class="ms-lg-3"><a href="{{route('login')}}" class="text-primary fw-bold">Entrar</a></li>
                <li><a href="{{route('register')}}" class="btn-get-started ms-2">Criar perfil</a></li>
            @else
                <li><a href="{{route('jobs.potential')}}" class="text-primary">Vagas Sugeridas</a></li>
                <li class="dropdown"><a href="#"><span>{{ Auth::user()->name }}</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
                    <ul>
                    <li><a href="{{route('profile.show')}}">Meu Perfil</a></li>
                    <li><a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Sair</a></li>
                    </ul>
                </li>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            @endguest
          </ul>
          <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>

      </div>

    </div>

  </header>

  <main class="main">

    @yield('content')

  </main>

  <footer id="footer" class="footer">

    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-5 col-md-12 footer-about">
          <a href="{{url('/')}}" class="d-flex align-items-center text-decoration-none">
            <span class="sitename">Angola Emprego</span>
          </a>
          <div class="footer-contact pt-3">
            <p>Conectando talentos às melhores oportunidades em Angola.</p>
            <p class="mt-3"><strong>Email:</strong> <span>geral@angolaemprego.com</span></p>
          </div>
        </div>

        <div class="col-lg-2 col-6 footer-links">
          <h4>Links Úteis</h4>
          <ul class="list-unstyled">
            <li><a href="{{url('/')}}" class="text-decoration-none text-muted">Início</a></li>
            <li><a href="{{url('/vagas')}}" class="text-decoration-none text-muted">Vagas</a></li>
            <li><a href="{{route('courses.index')}}" class="text-decoration-none text-muted">Cursos</a></li>
            <li><a href="{{url('/blog')}}" class="text-decoration-none text-muted">Blog</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-6 footer-links">
          <h4>Candidatos</h4>
          <ul class="list-unstyled">
            @guest
                <li><a href="{{route('register')}}" class="text-decoration-none text-muted">Criar Conta</a></li>
                <li><a href="{{route('login')}}" class="text-decoration-none text-muted">Entrar</a></li>
            @else
                <li><a href="{{route('profile.show')}}" class="text-decoration-none text-muted">Meu Perfil</a></li>
                <li><a href="{{route('jobs.potential')}}" class="text-decoration-none text-muted">Vagas Sugeridas</a></li>
            @endguest
          </ul>
        </div>

        <div class="col-lg-3 col-md-12">
          <h4>Siga-nos</h4>
          <p>Fique por dentro das novidades</p>
          <div class="social-links d-flex mt-2">
            <a href="https://www.linkedin.com/company/angola-emprego/" target="_blank" class="d-flex align-items-center justify-content-center bg-white text-primary border rounded-circle" style="width: 40px; height: 40px;"><i class="bi bi-linkedin"></i></a>
            <a href="https://www.facebook.com/61563942219094" target="_blank" class="d-flex align-items-center justify-content-center bg-white text-primary border rounded-circle ms-2" style="width: 40px; height: 40px;"><i class="bi bi-facebook"></i></a>
            <!-- <a href="#" class="d-flex align-items-center justify-content-center bg-white text-primary border rounded-circle ms-2" style="width: 40px; height: 40px;"><i class="bi bi-instagram"></i></a> -->
          </div>
        </div>

      </div>
    </div>
    
    <div class="container copyright text-center mt-4 pt-4 border-top">
      <p>&copy; <span>Copyright</span> <strong class="px-1">Angola Emprego</strong> <span>Todos os direitos reservados</span></p>
    </div>

  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/php-email-form/validate.js') }}"></script>
  <script src="{{ asset('assets/vendor/aos/aos.js') }}"></script>
  <script src="{{ asset('assets/vendor/glightbox/js/glightbox.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/waypoints/noframework.waypoints.js') }}"></script>
  <script src="{{ asset('assets/vendor/purecounter/purecounter_vanilla.js') }}"></script>
  <script src="{{ asset('assets/vendor/swiper/swiper-bundle.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/imagesloaded/imagesloaded.pkgd.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/isotope-layout/isotope.pkgd.min.js') }}"></script>

  <!-- Main JS File -->
  <script src="{{ asset('assets/js/main.js') }}"></script>
  @yield('footer-scripts')
</body>

</html>

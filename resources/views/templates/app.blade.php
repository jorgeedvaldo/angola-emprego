<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'pt' ? 'pt-AO' : app()->getLocale() }}">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>@yield('title') - {{env('APP_NAME')}}</title>
  <meta name="description" content="@yield('description')" />
  <meta name="csrf-token" content="{{ csrf_token() }}">

  @hasSection('canonical_link')
    <link rel="canonical" href="@yield('canonical_link')" />
  @endif

  <meta name="robots" content="follow, index, max-snippet:-1, max-video-preview:-1, max-image-preview:large" />

  <!-- Favicons -->
  <link rel="icon" href="{{ asset('assets/img/favicon.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('assets/img/apple-touch-icon.png') }}">

  <!-- RSS Feed Autodiscovery -->
  <link rel="alternate" type="application/rss+xml" title="Angola Emprego — Feed RSS" href="{{ url('/feed') }}" />

  <!-- Fonts -->
  <link rel="dns-prefetch" href="//fonts.googleapis.com">
  <link rel="dns-prefetch" href="//fonts.gstatic.com">
  <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- Vendor CSS Files -->
  <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">

  <link href="{{ asset('assets/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet">

  <!-- Google Analytcs tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-VW5BKCFM0R"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }
    gtag('js', new Date());

    gtag('config', 'G-VW5BKCFM0R');
  </script>

  <!-- AdSense -->
  <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2118765549976668"
    crossorigin="anonymous"></script>

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="@yield('og_type', 'website')" />
  <meta property="og:title" content="@yield('title') - {{env('APP_NAME')}}" />
  <meta property="og:url"
    content="@hasSection('canonical_link')@yield('canonical_link')@else{{ url()->current() }}@endif" />
  <meta property="og:description" content="@yield('description')" />
  @hasSection('created_at')
    <meta property="article:published_time" content="@yield('created_at')" />
  @endif
  @hasSection('updated_at')
    <meta property="article:modified_time" content="@yield('updated_at')" />
  @endif
  <meta property="og:site_name" content="Angola Emprego" />
  <meta property="og:image"
    content="@hasSection('og_image')@yield('og_image')@else{{ asset('assets/img/og-default.png') }}@endif" />
  <meta property="og:image:width" content="1200" />
  <meta property="og:image:height" content="630" />
  <meta property="og:image:alt" content="@yield('title') - Angola Emprego" />
  <meta property="og:locale" content="pt_AO" />
  <meta name="author" content="Angola Emprego" />

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="@yield('title') - {{env('APP_NAME')}}" />
  <meta name="twitter:description" content="@yield('description')" />
  <meta name="twitter:image"
    content="@hasSection('og_image')@yield('og_image')@else{{ asset('assets/img/og-default.png') }}@endif" />
  <meta name="twitter:image:alt" content="@yield('title') - Angola Emprego" />

  <script type="application/ld+json">
      {
      "@context": "https://schema.org",
      "@type": "Website",
      "name": "Angola Emprego - Notícias e Emprego",
      "url": "https://angolaemprego.com/"
    }
  </script>
  <script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "url": "https://www.angolaemprego.com",
  "sameAs": [
    "https://www.facebook.com/aoemprego"
  ],
  "logo": "https://angolaemprego.com/assets/img/logo.svg",
  "name": "Angola Emprego - Notícias e Emprego",
  "description": "Portal líder em recrutamento e ofertas de emprego em Angola, facilitando a conexão entre empresas e talentos em todo o país.",
  "email": "geral@angolaemprego.com",
  "telephone": "+244-951-014-936",
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "Luanda",
    "addressCountry": "AO"
  }
}
</script>

  @yield('head-scripts')
  <style>
    :root {
      --primary-color: #2557a7;
      /* Indeed Blue */
      --text-color: #2d2d2d;
      --bg-light: #f3f2f1;
    }

    body {
      font-family: 'Open Sans', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      color: var(--text-color);
      background-color: #f8f9fa;
    }

    .header {
      background: #fff;
      border-bottom: 1px solid #e4e2e0;
      padding: 0;
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
      height: 72px;
      display: flex;
      align-items: center;
    }

    .header .logo img {
      max-height: 38px;
    }

    /* Navmenu Desktop */
    .navmenu {
      flex-grow: 1;
      display: flex;
      justify-content: center;
    }

    .navmenu ul {
      margin: 0;
      padding: 0;
      display: flex;
      list-style: none;
      align-items: center;
      gap: 15px;
    }

    .navmenu ul li a {
      color: #595959;
      font-weight: 600;
      font-size: 14px;
      padding: 8px 16px;
      display: flex;
      align-items: center;
      gap: 8px;
      border-radius: 50px;
      transition: all 0.2s;
    }

    .navmenu ul li a i {
      font-size: 16px;
      color: #8c8c8c;
      transition: color 0.2s;
    }

    .navmenu ul li a:hover,
    .navmenu ul li a.active {
      color: var(--primary-color);
      background-color: #eaf1fb;
    }

    .navmenu ul li a:hover i,
    .navmenu ul li a.active i {
      color: var(--primary-color);
    }

    /* Buttons and User Actions */
    .header-actions {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .btn-create-cv {
      background-color: #fff;
      color: var(--primary-color);
      border: 1px solid #d4d2d0;
      font-weight: 700;
      padding: 8px 20px;
      border-radius: 8px;
      font-size: 14px;
      transition: all 0.2s;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .btn-create-cv:hover {
      background-color: #f3f2f1;
      border-color: #a4a2a0;
      color: var(--primary-color);
    }

    .btn-login {
      font-weight: 700;
      color: var(--primary-color);
      text-decoration: none;
      padding: 8px 16px;
      font-size: 14px;
    }

    .nav-profile-img {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      object-fit: cover;
      background-color: var(--primary-color);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 16px;
    }

    .mobile-nav-toggle {
      font-size: 28px;
      color: var(--text-color);
      margin-left: 15px;
      cursor: pointer;
      z-index: 9999;
    }

    body.mobile-nav-active .mobile-nav-toggle {
      color: #fff;
    }

    .footer {
      background: #fff;
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
    <div class="container-fluid container-xl d-flex align-items-center justify-content-between">

      <a href="{{url('/')}}" class="logo d-flex align-items-center text-decoration-none me-4">
        <img src="{{asset('assets/img/logo.svg')}}" alt="Angola Emprego - Notícias e Emprego"
          style="height: 48px; width: auto;">
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="{{url('/vagas')}}" class="{{ Request::is('vagas*') ? 'active' : '' }}"><i
                class="bi bi-briefcase"></i> {{ __('site.nav.vagas') }}</a></li>
          <li><a href="{{route('courses.index')}}" class="{{ Request::is('cursos*') ? 'active' : '' }}"><i
                class="bi bi-journal-bookmark"></i> {{ __('site.nav.cursos') }}</a></li>
          <li><a href="{{url('/noticias')}}"
              class="{{ Request::is('noticias*') || Request::is('blog*') ? 'active' : '' }}"><i
                class="bi bi-newspaper"></i> {{ __('site.nav.noticias') }}</a></li>
          <li><a href="{{route('recruiters.index')}}"
              class="{{ Request::is('empresas*') || Request::is('company*') || Request::is('analisador-de-cv*') ? 'active' : '' }}"><i
                class="bi bi-building"></i> {{ __('site.nav.recrutadores') }}</a></li>

          <!-- Mobile Only Actions -->
          <li class="d-xl-none"><a href="{{route('cv-analyzer.index')}}"><i class="bi bi-stars"></i> {{ __('site.nav.analisar_cvs') }}</a></li>
          @guest
            <li class="d-xl-none"><a href="{{route('login')}}"><i class="bi bi-box-arrow-in-right"></i> {{ __('site.nav.entrar') }}</a></li>
            <li class="d-xl-none"><a href="{{route('register')}}"><i class="bi bi-person-plus"></i> {{ __('site.nav.criar_conta') }}</a></li>
            <li class="d-xl-none"><a href="{{route('register.company')}}"><i class="bi bi-building-add"></i> {{ __('site.nav.sou_empresa') }}</a></li>
          @else
            @if(Auth::user()->isCompany())
            <li class="d-xl-none"><a href="{{route('company.dashboard')}}"><i class="bi bi-building"></i> {{ __('site.nav.painel_empresa') }}</a></li>
            @else
            <li class="d-xl-none"><a href="{{route('profile.show')}}"><i class="bi bi-person"></i> {{ __('site.nav.meu_perfil') }}</a></li>
            <li class="d-xl-none"><a href="{{route('jobs.potential')}}"><i class="bi bi-stars"></i> {{ __('site.nav.vagas_sugeridas') }}</a>
            </li>
            @endif
            <li class="d-xl-none">
              <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                class="text-danger">
                <i class="bi bi-box-arrow-right"></i> {{ __('site.nav.sair') }}
              </a>
            </li>
          @endguest
        </ul>
      </nav>

      <div class="header-actions">
        @guest
          <a href="{{route('login')}}" class="btn-login d-none d-md-block">{{ __('site.nav.entrar') }}</a>
          <a href="{{route('register.company')}}" class="btn-create-cv d-none d-lg-flex" title="Para empresas">
            <i class="bi bi-building"></i> {{ __('site.nav.sou_empresa') }}
          </a>
          <a href="{{route('register')}}" class="btn-create-cv">
            <i class="bi bi-person-plus"></i> {{ __('site.nav.criar_conta') }}
          </a>
        @else
          @if(Auth::user()->isCompany())
          <a href="{{route('company.dashboard')}}" class="btn-create-cv d-none d-md-flex" title="Painel da empresa">
            <i class="bi bi-building"></i> {{ __('site.nav.painel_empresa') }}
          </a>
          @else
          <a href="{{route('profile.show')}}" class="btn-create-cv d-none d-md-flex" title="Ver Perfil">
            <i class="bi bi-person-badge"></i> {{ __('site.nav.meu_perfil') }}
          </a>
          @endif

          <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none" id="dropdownUser1"
              data-bs-toggle="dropdown" aria-expanded="false">
              @if(Auth::user()->avatar)
                <img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}" class="nav-profile-img">
              @else
                <div class="nav-profile-img">
                  {{ substr(Auth::user()->name, 0, 1) }}
                </div>
              @endif
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="dropdownUser1">
              <li class="px-3 py-2 border-bottom">
                <div class="fw-bold text-dark">{{ Auth::user()->name }}</div>
                <div class="text-muted small">{{ Auth::user()->email }}</div>
              </li>
              @if(Auth::user()->isCompany())
              <li><a class="dropdown-item py-2" href="{{route('company.dashboard')}}"><i class="bi bi-building me-2"></i> {{ __('site.nav.painel_empresa') }}</a></li>
              @if(Auth::user()->company?->isPublic())
              <li><a class="dropdown-item py-2" href="{{ url('/company/' . Auth::user()->company->slug) }}"><i class="bi bi-box-arrow-up-right me-2"></i> {{ __('site.nav.ver_pagina') }}</a></li>
              @endif
              @else
              <li><a class="dropdown-item py-2" href="{{route('profile.show')}}"><i class="bi bi-person me-2"></i> {{ __('site.nav.meu_perfil') }}</a></li>
              <li><a class="dropdown-item py-2" href="{{route('jobs.potential')}}"><i class="bi bi-stars me-2"></i> {{ __('site.nav.vagas_sugeridas') }}</a></li>
              @endif
              <li><a class="dropdown-item py-2" href="{{route('cv-analyzer.index')}}"><i class="bi bi-stars me-2"></i> {{ __('site.nav.analisar_cvs') }}</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li>
                <a class="dropdown-item py-2 text-danger" href="#"
                  onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                  <i class="bi bi-box-arrow-right me-2"></i> {{ __('site.nav.sair') }}
                </a>
              </li>
            </ul>
          </div>
          <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
          </form>
        @endguest
        <i class="mobile-nav-toggle d-xl-none bi bi-list ms-3 cursor-pointer"></i>
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
            <img src="{{asset('assets/img/logo.svg')}}" alt="Angola Emprego - Notícias e Emprego"
              style="height: 48px; width: auto;">
          </a>
          <div class="footer-contact pt-3">
            <p>{{ __('site.footer.sobre_o_site') }}</p>
            <p class="mt-3"><strong>Email:</strong> <span>geral@angolaemprego.com</span></p>
          </div>
        </div>

        <div class="col-lg-2 col-6 footer-links">
          <h4>{{ __('site.footer.links_uteis') }}</h4>
          <ul class="list-unstyled">
            <li><a href="{{url('/')}}" class="text-decoration-none text-muted">{{ __('site.footer.inicio') }}</a></li>
            <li><a href="{{url('/sobre')}}" class="text-decoration-none text-muted">{{ __('site.footer.sobre') }}</a></li>
            <li><a href="{{url('/vagas')}}" class="text-decoration-none text-muted">{{ __('site.nav.vagas') }}</a></li>
            <li><a href="{{route('companies.index')}}" class="text-decoration-none text-muted">{{ __('site.footer.empresas') }}</a></li>
            <li><a href="{{route('recruiters.index')}}" class="text-decoration-none text-muted">{{ __('site.nav.recrutadores') }}</a></li>
            <li><a href="{{route('courses.index')}}" class="text-decoration-none text-muted">{{ __('site.nav.cursos') }}</a></li>
            <li><a href="{{url('/noticias')}}" class="text-decoration-none text-muted">{{ __('site.nav.noticias') }}</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-6 footer-links">
          <h4>{{ __('site.footer.candidatos') }}</h4>
          <ul class="list-unstyled">
            @guest
              <li><a href="{{route('register')}}" class="text-decoration-none text-muted">{{ __('site.nav.criar_conta') }}</a></li>
              <li><a href="{{route('login')}}" class="text-decoration-none text-muted">{{ __('site.nav.entrar') }}</a></li>
            @else
              @if(Auth::user()->isCompany())
                <li><a href="{{route('company.dashboard')}}" class="text-decoration-none text-muted">{{ __('site.nav.painel_empresa') }}</a></li>
              @else
                <li><a href="{{route('profile.show')}}" class="text-decoration-none text-muted">{{ __('site.nav.meu_perfil') }}</a></li>
                <li><a href="{{route('jobs.potential')}}" class="text-decoration-none text-muted">{{ __('site.nav.vagas_sugeridas') }}</a></li>
              @endif
            @endguest
          </ul>
        </div>

        <div class="col-lg-3 col-md-12">
          <h4>{{ __('site.footer.siga_nos') }}</h4>
          <p>{{ __('site.footer.novidades') }}</p>
          <div class="social-links d-flex mt-2">
            <a href="https://www.linkedin.com/company/angola-emprego/" target="_blank"
              class="d-flex align-items-center justify-content-center bg-white text-primary border rounded-circle"
              style="width: 40px; height: 40px;"><i class="bi bi-linkedin"></i></a>
            <a href="https://www.facebook.com/61563942219094" target="_blank"
              class="d-flex align-items-center justify-content-center bg-white text-primary border rounded-circle ms-2"
              style="width: 40px; height: 40px;"><i class="bi bi-facebook"></i></a>
            <!-- <a href="#" class="d-flex align-items-center justify-content-center bg-white text-primary border rounded-circle ms-2" style="width: 40px; height: 40px;"><i class="bi bi-instagram"></i></a> -->
          </div>
        </div>

      </div>
    </div>

    <div class="container copyright mt-4 pt-4 border-top">
      <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
        <p class="mb-0 text-center text-md-start">&copy; <span>Copyright</span> <strong class="px-1">Angola Emprego -
            Notícias e Emprego</strong> <span>{{ __('site.footer.direitos') }}</span></p>

        {{-- Selector de idioma. Cada idioma escreve-se no seu próprio nome: quem
             não lê a língua em que a página está tem de conseguir reconhecer a
             sua. O idioma actual não é uma ligação, para não parecer que clicar
             nele faz alguma coisa. --}}
        <nav class="language-switcher d-flex align-items-center gap-2" aria-label="{{ __('site.footer.idioma') }}">
          <span class="small text-muted"><i class="bi bi-translate me-1"></i>{{ __('site.footer.idioma') }}:</span>
          @foreach(\App\Http\Middleware\SetLocale::suportados() as $codigo => $idioma)
            @if($codigo === app()->getLocale())
              <span class="badge bg-white text-dark border" aria-current="true">{{ $idioma['bandeira'] }} {{ $idioma['nativo'] }}</span>
            @else
              <a href="{{ route('locale.switch', $codigo) }}" class="badge bg-light text-muted border text-decoration-none"
                 hreflang="{{ $codigo }}" rel="alternate">{{ $idioma['bandeira'] }} {{ $idioma['nativo'] }}</a>
            @endif
          @endforeach
        </nav>
      </div>
    </div>

  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
      class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/php-email-form/validate.js') }}"></script>

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
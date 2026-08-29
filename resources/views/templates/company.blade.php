<!DOCTYPE html>
<html lang="pt-AO">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>@yield('title') - {{ $company->name }}</title>
  <meta name="description" content="@yield('description')" />

  @hasSection('canonical_link')
    <link rel="canonical" href="@yield('canonical_link')" />
  @endif

  <meta name="robots" content="follow, index, max-snippet:-1, max-image-preview:large" />

  <link rel="icon" href="{{ $company->logo_url ?: asset('assets/img/favicon.png') }}">

  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800&display=swap"
    rel="stylesheet">

  <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">

  <meta property="og:type" content="website" />
  <meta property="og:title" content="@yield('title') - {{ $company->name }}" />
  <meta property="og:url"
    content="@hasSection('canonical_link')@yield('canonical_link')@else{{ url()->current() }}@endif" />
  <meta property="og:description" content="@yield('description')" />
  <meta property="og:site_name" content="{{ $company->name }}" />
  @if($company->logo)
    <meta property="og:image" content="{{ $company->logo_url }}" />
  @endif
  <meta property="og:locale" content="pt_AO" />

  @yield('head-scripts')

  <style>
    body {
      font-family: 'Open Sans', system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
      color: #2d2d2d;
      background-color: #fff;
    }

    .company-nav {
      background: #fff;
      border-bottom: 1px solid #e6eaf0;
      padding: 14px 0;
    }

    .company-nav-brand {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
      color: #1b2431;
    }

    .company-nav-logo {
      width: 46px;
      height: 46px;
      border-radius: 10px;
      background: #f2f5fa;
      border: 1px solid #e6eaf0;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      flex-shrink: 0;
    }

    .company-nav-logo img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      padding: 5px;
    }

    .company-nav-logo span {
      font-weight: 800;
      font-size: 1.2rem;
      color: #2557a7;
    }

    .company-nav-name {
      font-weight: 800;
      font-size: 1.05rem;
      line-height: 1.2;
    }

    .company-nav-tag {
      font-size: .74rem;
      letter-spacing: .1em;
      text-transform: uppercase;
      color: #8792a2;
      font-weight: 700;
    }

    .company-nav-social {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 38px;
      height: 38px;
      border-radius: 50%;
      border: 1px solid #dfe5ee;
      color: #2557a7;
      font-size: 1rem;
      text-decoration: none;
      transition: .2s ease;
    }

    .company-nav-social:hover {
      background: #2557a7;
      border-color: #2557a7;
      color: #fff;
      transform: translateY(-2px);
    }

    .company-nav-cta {
      font-weight: 700;
      font-size: .9rem;
      color: #2557a7;
      border: 1px solid #cadaf1;
      border-radius: 50px;
      padding: 8px 18px;
      text-decoration: none;
      white-space: nowrap;
    }

    .company-nav-cta:hover {
      background: #eef4fd;
      color: #1c4585;
    }

    .company-footer {
      background: #101b2d;
      color: #b9c3d3;
      padding: 44px 0 28px;
    }

    .company-footer a {
      color: #d7dfeb;
      text-decoration: none;
    }

    .company-footer a:hover {
      color: #fff;
    }

    .company-footer-social {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      border: 1px solid rgba(255, 255, 255, .18);
      font-size: 1.05rem;
      transition: .2s ease;
    }

    .company-footer-social:hover {
      background: rgba(255, 255, 255, .12);
      transform: translateY(-2px);
    }

    .company-footer-bottom {
      border-top: 1px solid rgba(255, 255, 255, .1);
      margin-top: 28px;
      padding-top: 18px;
      font-size: .85rem;
      color: #8a97ab;
    }

    main {
      min-height: 70vh;
    }
  </style>
</head>

<body>

  <header class="company-nav sticky-top">
    <div class="container d-flex align-items-center justify-content-between gap-3">

      <a href="{{ route('companies.show', $company->slug) }}" class="company-nav-brand">
        <span class="company-nav-logo">
          @if($company->logo)
            <img src="{{ $company->logo_url }}" alt="Logótipo {{ $company->name }}">
          @else
            <span>{{ strtoupper(substr($company->name, 0, 1)) }}</span>
          @endif
        </span>
        <span>
          <span class="company-nav-name d-block">{{ $company->name }}</span>
          <span class="company-nav-tag">Carreiras</span>
        </span>
      </a>

      <div class="d-flex align-items-center gap-2">
        @if($company->linkedin_url)
          <a href="{{ $company->linkedin_url }}" target="_blank" rel="noopener noreferrer" class="company-nav-social"
            aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
        @endif
        @if($company->facebook_url)
          <a href="{{ $company->facebook_url }}" target="_blank" rel="noopener noreferrer" class="company-nav-social"
            aria-label="Facebook"><i class="bi bi-facebook"></i></a>
        @endif
        @if($company->instagram_url)
          <a href="{{ $company->instagram_url }}" target="_blank" rel="noopener noreferrer" class="company-nav-social"
            aria-label="Instagram"><i class="bi bi-instagram"></i></a>
        @endif
        @if($company->website)
          <a href="{{ $company->website }}" target="_blank" rel="noopener noreferrer" class="company-nav-social"
            aria-label="Website"><i class="bi bi-globe"></i></a>
        @endif

        <a href="{{ route('companies.show', $company->slug) }}#vagas" class="company-nav-cta d-none d-sm-inline-block">Vagas
          abertas</a>
      </div>

    </div>
  </header>

  <main>
    @yield('content')
  </main>

  <footer class="company-footer">
    <div class="container">
      <div class="row gy-4 align-items-start">

        <div class="col-md-6">
          <div class="d-flex align-items-center gap-3 mb-3">
            <span class="company-nav-logo">
              @if($company->logo)
                <img src="{{ $company->logo_url }}" alt="Logótipo {{ $company->name }}">
              @else
                <span>{{ strtoupper(substr($company->name, 0, 1)) }}</span>
              @endif
            </span>
            <span class="fw-bold text-white fs-5">{{ $company->name }}</span>
          </div>
          @if($company->headline)
            <p class="mb-0" style="max-width: 460px;">{{ $company->headline }}</p>
          @endif
        </div>

        <div class="col-md-3">
          <h6 class="text-white fw-bold mb-3">Contactos</h6>
          <ul class="list-unstyled mb-0">
            @if($company->location)
              <li class="mb-2"><i class="bi bi-geo-alt me-2"></i>{{ $company->location }}</li>
            @endif
            @if($company->email)
              <li class="mb-2"><i class="bi bi-envelope me-2"></i><a
                  href="mailto:{{ $company->email }}">{{ $company->email }}</a></li>
            @endif
            @if($company->phone)
              <li class="mb-2"><i class="bi bi-telephone me-2"></i><a
                  href="tel:{{ $company->phone }}">{{ $company->phone }}</a></li>
            @endif
          </ul>
        </div>

        <div class="col-md-3">
          <h6 class="text-white fw-bold mb-3">Siga-nos</h6>
          <div class="d-flex flex-wrap gap-2">
            @if($company->linkedin_url)
              <a href="{{ $company->linkedin_url }}" target="_blank" rel="noopener noreferrer"
                class="company-footer-social" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
            @endif
            @if($company->facebook_url)
              <a href="{{ $company->facebook_url }}" target="_blank" rel="noopener noreferrer"
                class="company-footer-social" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
            @endif
            @if($company->instagram_url)
              <a href="{{ $company->instagram_url }}" target="_blank" rel="noopener noreferrer"
                class="company-footer-social" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
            @endif
            @if($company->website)
              <a href="{{ $company->website }}" target="_blank" rel="noopener noreferrer" class="company-footer-social"
                aria-label="Website"><i class="bi bi-globe"></i></a>
            @endif
          </div>
        </div>

      </div>

      <div class="company-footer-bottom d-flex flex-column flex-md-row justify-content-between gap-2">
        <span>&copy; {{ date('Y') }} {{ $company->name }}. Todos os direitos reservados.</span>
        <a href="{{ route('companies.show', $company->slug) }}#vagas">Ver vagas abertas</a>
      </div>
    </div>
  </footer>

  <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  @yield('footer-scripts')
</body>

</html>

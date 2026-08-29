@extends('templates.company')
@section('title', 'Carreiras')
@section('description', Str::limit(strip_tags($company->headline ?: $company->description ?: 'Vagas abertas na ' . $company->name), 160))
@section('canonical_link', url('/company/' . $company->slug))

@section('content')
<section class="company-careers-hero">
    <div class="container py-4 py-lg-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb company-breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="{{ route('companies.show', $company->slug) }}">{{ $company->name }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Carreiras</li>
            </ol>
        </nav>

        <div class="row align-items-center gy-4 py-lg-3">
            <div class="col-lg-8">
                <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-4">
                    <div class="company-logo-wrap flex-shrink-0">
                        @if($company->logo)
                            <img src="{{ $company->logo_url }}" alt="Logótipo {{ $company->name }}">
                        @else
                            <span>{{ strtoupper(substr($company->name, 0, 1)) }}</span>
                        @endif
                    </div>
                    <div>
                        <p class="company-eyebrow mb-2">Carreiras na {{ $company->name }}</p>
                        <h1 class="display-5 fw-bold mb-3">{{ $company->headline ?: 'Faça parte da nossa equipa' }}</h1>
                        <div class="d-flex flex-wrap gap-3 company-meta">
                            @if($company->location)
                                <span><i class="bi bi-geo-alt"></i> {{ $company->location }}</span>
                            @endif
                            <span><i class="bi bi-briefcase"></i> {{ $jobs->total() }} {{ $jobs->total() === 1 ? 'vaga aberta' : 'vagas abertas' }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="#vagas" class="btn btn-light btn-lg rounded-pill fw-bold px-4">
                    Ver vagas abertas <i class="bi bi-arrow-down ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container">
        <div class="row gy-5">
            <div class="col-lg-8">
                <p class="company-section-label mb-2">Quem somos</p>
                <h2 class="fw-bold mb-4">Sobre a {{ $company->name }}</h2>
                <div class="company-about">
                    @if($company->description)
                        {!! nl2br(e($company->description)) !!}
                    @else
                        <p class="text-muted">Conheça as oportunidades disponíveis e encontre o seu próximo desafio profissional na {{ $company->name }}.</p>
                    @endif
                </div>
            </div>
            <div class="col-lg-4">
                <aside class="company-info-card">
                    <h5 class="fw-bold mb-3">Conheça-nos melhor</h5>
                    @if($company->website)
                        <a href="{{ $company->website }}" target="_blank" rel="noopener noreferrer" class="company-contact-link">
                            <i class="bi bi-globe"></i><span>Visitar website</span>
                        </a>
                    @endif
                    @if($company->email)
                        <a href="mailto:{{ $company->email }}" class="company-contact-link">
                            <i class="bi bi-envelope"></i><span>{{ $company->email }}</span>
                        </a>
                    @endif
                    @if($company->phone)
                        <a href="tel:{{ $company->phone }}" class="company-contact-link">
                            <i class="bi bi-telephone"></i><span>{{ $company->phone }}</span>
                        </a>
                    @endif

                    @if($company->linkedin_url || $company->facebook_url || $company->instagram_url)
                        <div class="border-top mt-3 pt-3">
                            <p class="small fw-semibold text-muted mb-2">Siga a empresa</p>
                            <div class="d-flex gap-2">
                                @if($company->linkedin_url)
                                    <a href="{{ $company->linkedin_url }}" target="_blank" rel="noopener noreferrer" class="company-social-link" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                                @endif
                                @if($company->facebook_url)
                                    <a href="{{ $company->facebook_url }}" target="_blank" rel="noopener noreferrer" class="company-social-link" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                                @endif
                                @if($company->instagram_url)
                                    <a href="{{ $company->instagram_url }}" target="_blank" rel="noopener noreferrer" class="company-social-link" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                                @endif
                            </div>
                        </div>
                    @endif
                </aside>
            </div>
        </div>
    </div>
</section>

<section id="vagas" class="py-5 company-jobs-section">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-2 mb-4">
            <div>
                <p class="company-section-label mb-2">Oportunidades</p>
                <h2 class="fw-bold mb-0">Vagas abertas</h2>
            </div>
            <span class="text-muted">{{ $jobs->total() }} {{ $jobs->total() === 1 ? 'oportunidade' : 'oportunidades' }}</span>
        </div>

        @forelse($jobs as $job)
            <a href="{{ route('companies.job', [$company->slug, $job->slug]) }}" class="company-job-card">
                <div>
                    <h5 class="fw-bold mb-2">{{ $job->title }}</h5>
                    <div class="company-job-meta">
                        <span><i class="bi bi-geo-alt"></i> {{ $job->location }}</span>
                        <span><i class="bi bi-calendar3"></i> Publicada em {{ $job->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
                <span class="company-job-arrow"><i class="bi bi-arrow-right"></i></span>
            </a>
        @empty
            <div class="company-empty-jobs text-center">
                <i class="bi bi-briefcase display-5"></i>
                <h5 class="fw-bold mt-3">Sem vagas abertas neste momento</h5>
                <p class="text-muted mb-0">Volte em breve para conhecer novas oportunidades.</p>
            </div>
        @endforelse

        <div class="mt-4">{{ $jobs->links() }}</div>
    </div>
</section>

<style>
    .company-careers-hero {
        color: #fff;
        background: linear-gradient(135deg, #173c78 0%, #2557a7 55%, #3575ce 100%);
        position: relative;
        overflow: hidden;
    }
    .company-careers-hero::after {
        content: "";
        position: absolute;
        width: 420px;
        height: 420px;
        right: -130px;
        top: -200px;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
    }
    .company-breadcrumb { position: relative; z-index: 1; }
    .company-breadcrumb a { color: rgba(255,255,255,.78); text-decoration: none; }
    .company-breadcrumb .active { color: #fff; }
    .company-breadcrumb .breadcrumb-item + .breadcrumb-item::before { color: rgba(255,255,255,.5); }
    .company-logo-wrap {
        width: 112px;
        height: 112px;
        border-radius: 22px;
        background: #fff;
        border: 1px solid rgba(255,255,255,.55);
        box-shadow: 0 16px 40px rgba(10,32,68,.25);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .company-logo-wrap img { width: 100%; height: 100%; object-fit: contain; padding: 12px; }
    .company-logo-wrap span { color: #2557a7; font-size: 2.4rem; font-weight: 800; }
    .company-eyebrow, .company-section-label {
        text-transform: uppercase;
        letter-spacing: .12em;
        font-size: .75rem;
        font-weight: 800;
    }
    .company-eyebrow { color: #cde0ff; }
    .company-section-label { color: #2557a7; }
    .company-meta { color: rgba(255,255,255,.82); font-size: .95rem; }
    .company-meta span, .company-job-meta span { display: inline-flex; align-items: center; gap: .4rem; }
    .company-about { color: #505967; font-size: 1.06rem; line-height: 1.85; white-space: normal; }
    .company-info-card {
        background: #f7f9fc;
        border: 1px solid #e5eaf1;
        border-radius: 16px;
        padding: 1.5rem;
    }
    .company-contact-link {
        display: flex;
        align-items: center;
        gap: .7rem;
        color: #3f4957;
        text-decoration: none;
        padding: .55rem 0;
        word-break: break-word;
    }
    .company-contact-link:hover { color: #2557a7; }
    .company-social-link {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: #2557a7;
        background: #fff;
        border: 1px solid #dce3ed;
        font-size: 1.1rem;
        transition: .2s ease;
    }
    .company-social-link:hover { color: #fff; background: #2557a7; transform: translateY(-2px); }
    .company-jobs-section { background: #f5f7fa; }
    .company-job-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        padding: 1.4rem 1.5rem;
        margin-bottom: .85rem;
        color: #202833;
        background: #fff;
        border: 1px solid #e5e9ef;
        border-radius: 14px;
        text-decoration: none;
        transition: .2s ease;
    }
    .company-job-card:hover {
        color: #2557a7;
        border-color: #aac4ec;
        box-shadow: 0 10px 28px rgba(37,87,167,.09);
        transform: translateY(-2px);
    }
    .company-job-meta { display: flex; flex-wrap: wrap; gap: 1rem; color: #737d8b; font-size: .88rem; }
    .company-job-arrow {
        width: 42px;
        height: 42px;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #eef4fd;
        color: #2557a7;
    }
    .company-empty-jobs { background: #fff; border: 1px dashed #cad2df; border-radius: 16px; padding: 3.5rem 1rem; color: #8a94a3; }
    @media (max-width: 575.98px) {
        .company-logo-wrap { width: 88px; height: 88px; }
        .company-careers-hero .display-5 { font-size: 2rem; }
        .company-job-card { align-items: flex-start; }
    }
</style>
@endsection

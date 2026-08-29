@extends('templates.company')
@section('title', $job->title)
@section('description', Str::limit(strip_tags($job->description), 160))
@section('canonical_link', route('companies.job', [$company->slug, $job->slug]))

@section('head-scripts')
<script type="application/ld+json">
{
  "@context": "https://schema.org/",
  "@type": "JobPosting",
  "title": "{{ $job->title }}",
  "description": "{{ Str::limit(strip_tags($job->description), 5000) }}",
  "datePosted": "{{ $job->created_at->toIso8601String() }}",
  "validThrough": "{{ $job->created_at->addMonths(2)->toIso8601String() }}",
  "employmentType": ["FULL_TIME"],
  "hiringOrganization": {
    "@type": "Organization",
    "name": "{{ $company->name }}",
    "logo": "{{ $company->logo_url }}",
    "sameAs": "{{ $company->website ?: route('companies.show', $company->slug) }}"
  },
  "jobLocation": {
    "@type": "Place",
    "address": {
      "@type": "PostalAddress",
      "addressLocality": "{{ $job->location ?: 'Luanda' }}",
      "addressCountry": "AO"
    }
  }
}
</script>
@endsection

@section('content')
<section class="company-job-hero">
    <div class="container py-4 py-lg-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb company-breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="{{ route('companies.show', $company->slug) }}">{{ $company->name }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('companies.show', $company->slug) }}#vagas">Carreiras</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $job->title }}</li>
            </ol>
        </nav>

        <h1 class="display-6 fw-bold mb-3">{{ $job->title }}</h1>
        <div class="d-flex flex-wrap gap-3 company-meta">
            <span><i class="bi bi-geo-alt"></i> {{ $job->location }}</span>
            <span><i class="bi bi-calendar3"></i> Publicada em {{ $job->created_at->format('d/m/Y') }}</span>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row gy-4">
            <div class="col-lg-8">
                <div class="company-panel mb-4">
                    <h2 class="h4 fw-bold mb-3">Descrição da vaga</h2>
                    <div class="company-job-description">{!! $job->description !!}</div>
                </div>

                @php
                    $maxAttachments = $company->allowedAttachmentCount();
                @endphp

                <div id="candidatura" class="company-panel">
                    <h2 class="h4 fw-bold mb-3">Enviar candidatura</h2>
                    <p class="text-muted small">
                        Preencha o assunto, a mensagem e anexe até <strong>{{ $maxAttachments }}</strong>
                        {{ $maxAttachments === 1 ? 'ficheiro' : 'ficheiros' }}
                        (PDF, DOC ou DOCX, até 5 MB cada).
                    </p>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ route('jobs.apply', $job->slug) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nome</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', auth()->user()->name ?? '') }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', auth()->user()->email ?? '') }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Telefone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', auth()->user()->mobile ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Assunto</label>
                                <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject', 'Candidatura — ' . $job->title) }}" required>
                                @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Mensagem</label>
                                <textarea name="message" rows="5" class="form-control @error('message') is-invalid @enderror" required>{{ old('message') }}</textarea>
                                @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Anexos</label>
                                @for($i = 0; $i < $maxAttachments; $i++)
                                    <input type="file"
                                        name="attachments[]"
                                        class="form-control mb-2 @error('attachments') is-invalid @enderror @error('attachments.'.$i) is-invalid @enderror"
                                        accept=".pdf,.doc,.docx,application/pdf"
                                        @if($i === 0) required @endif>
                                    <small class="text-muted d-block mb-2">
                                        {{ $i === 0 ? 'Obrigatório (CV)' : 'Opcional (certificado, carta, etc.)' }}
                                    </small>
                                @endfor
                                @error('attachments')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                @error('attachments.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary fw-bold px-4" style="background-color: #2557a7; border-color: #2557a7;">
                                    <i class="bi bi-send me-1"></i> Enviar candidatura
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-4">
                <aside class="company-panel">
                    <h3 class="h6 fw-bold mb-3">Sobre a {{ $company->name }}</h3>
                    @if($company->description)
                        <p class="text-muted small mb-3">{{ Str::limit(strip_tags($company->description), 240) }}</p>
                    @endif
                    <a href="{{ route('companies.show', $company->slug) }}" class="fw-semibold text-decoration-none">
                        Ver todas as vagas <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </aside>
            </div>
        </div>
    </div>
</section>

<style>
    .company-job-hero {
        color: #fff;
        background: linear-gradient(135deg, #173c78 0%, #2557a7 55%, #3575ce 100%);
    }
    .company-breadcrumb a { color: rgba(255,255,255,.78); text-decoration: none; }
    .company-breadcrumb .active { color: #fff; }
    .company-breadcrumb .breadcrumb-item + .breadcrumb-item::before { color: rgba(255,255,255,.5); }
    .company-meta { color: rgba(255,255,255,.85); font-size: .95rem; }
    .company-meta span { display: inline-flex; align-items: center; gap: .4rem; }
    .company-panel {
        background: #fff;
        border: 1px solid #e5e9ef;
        border-radius: 16px;
        padding: 1.75rem;
    }
    .company-job-description { color: #505967; font-size: 1.04rem; line-height: 1.8; }
    .company-job-description h1,
    .company-job-description h2,
    .company-job-description h3 { font-weight: 700; font-size: 1.2rem; margin: 1.4rem 0 .8rem; color: #202833; }
    .company-job-description ul { padding-left: 1.4rem; }
</style>
@endsection

@extends('templates.app')

@section('title', __('site.erros.403_meta'))
@section('description', __('site.erros.403_meta_descricao'))

@section('content')
<section class="section py-5 d-flex align-items-center" style="min-height: 70vh;">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <!-- Icon or Illustration -->
                <div class="mb-4">
                    <i class="bi bi-shield-lock display-1 text-muted opacity-50"></i>
                </div>

                <h1 class="display-4 fw-bold mb-3 text-dark">403</h1>
                <h2 class="h4 text-muted mb-4">{{ __('site.erros.403_titulo') }}</h2>

                <p class="lead text-muted mb-5">
                    {{ __('site.erros.403_texto') }}
                </p>

                <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                    <a href="{{ url('/') }}" class="btn btn-primary btn-lg px-4 gap-3 rounded-pill">
                        <i class="bi bi-house-door me-2"></i>{{ __('site.erros.voltar_inicio') }}
                    </a>
                    <a href="{{ url('/vagas') }}" class="btn btn-outline-secondary btn-lg px-4 rounded-pill">
                        <i class="bi bi-search me-2"></i>{{ __('site.erros.ver_vagas') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

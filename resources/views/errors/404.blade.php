@extends('templates.app')

@section('title', 'Página Não Encontrada (404)')
@section('description', 'A página que procura não foi encontrada.')

@section('content')
<section class="section py-5 d-flex align-items-center" style="min-height: 70vh;">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <!-- Icon or Illustration -->
                <div class="mb-4">
                    <i class="bi bi-slash-circle display-1 text-muted opacity-50"></i>
                </div>
                
                <h1 class="display-4 fw-bold mb-3 text-dark">404</h1>
                <h2 class="h4 text-muted mb-4">Página Não Encontrada</h2>
                
                <p class="lead text-muted mb-5">
                    Lamentamos, mas a página que está à procura não existe, foi movida ou está temporariamente indisponível.
                </p>

                <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                    <a href="{{ url('/') }}" class="btn btn-primary btn-lg px-4 gap-3 rounded-pill">
                        <i class="bi bi-house-door me-2"></i>Voltar ao Início
                    </a>
                    <a href="{{ url('/vagas') }}" class="btn btn-outline-secondary btn-lg px-4 rounded-pill">
                        <i class="bi bi-search me-2"></i>Ver Vagas
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

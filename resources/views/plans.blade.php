@extends('templates.app')

@section('title', 'Planos de Subscrição')

@section('content')
<section class="py-5 bg-light">
    <div class="container py-5">
        <div class="row text-center justify-content-center mb-5">
             <div class="col-lg-8">
                 <h1 class="fw-bold text-dark">Escolha o Seu Plano</h1>
                 <p class="text-muted lead">Receba candidaturas automáticas e aumente as suas chances de conseguir um emprego.</p>
             </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show text-center mb-5" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row row-cols-1 row-cols-md-4 mb-3 text-center">
            
            <!-- Semanal -->
            <div class="col">
              <div class="card mb-4 rounded-3 shadow-sm border-0 h-100">
                <div class="card-header py-3 bg-white border-0">
                  <h4 class="my-0 fw-normal">Semanal</h4>
                </div>
                <div class="card-body d-flex flex-column">
                  <h1 class="card-title pricing-card-title">1.000 <small class="text-muted fw-light">Kz</small></h1>
                  <ul class="list-unstyled mt-3 mb-4 flex-grow-1">
                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Candidaturas Automáticas</li>
                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Válido por 7 dias</li>
                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Suporte Prioritário</li>
                  </ul>
                  <a href="{{ route('plans.confirm', ['plan' => 'weekly']) }}" class="w-100 btn btn-lg btn-outline-primary rounded-pill">Escolher Semanal</a>
                </div>
              </div>
            </div>

            <!-- Mensal -->
            <div class="col">
              <div class="card mb-4 rounded-3 shadow border-primary h-100 border-2">
                <div class="card-header py-3 bg-primary text-white border-primary">
                  <h4 class="my-0 fw-normal">Mensal</h4>
                </div>
                <div class="card-body d-flex flex-column">
                  <h1 class="card-title pricing-card-title">3.000 <small class="text-muted fw-light">Kz</small></h1>
                  <ul class="list-unstyled mt-3 mb-4 flex-grow-1">
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i>Candidaturas Automáticas</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i>Válido por 30 dias</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i>Destaque no Perfil</li>
                  </ul>
                  <a href="{{ route('plans.confirm', ['plan' => 'monthly']) }}" class="w-100 btn btn-lg btn-primary rounded-pill">Escolher Mensal</a>
                </div>
              </div>
            </div>

            <!-- Trimestral -->
            <div class="col">
              <div class="card mb-4 rounded-3 shadow-sm border-0 h-100">
                <div class="card-header py-3 bg-white border-0">
                  <h4 class="my-0 fw-normal">Trimestral</h4>
                </div>
                <div class="card-body d-flex flex-column">
                  <h1 class="card-title pricing-card-title">6.950 <small class="text-muted fw-light">Kz</small></h1>
                  <ul class="list-unstyled mt-3 mb-4 flex-grow-1">
                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Candidaturas Automáticas</li>
                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Válido por 90 dias</li>
                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Poupança de 3.000 Kz</li>
                  </ul>
                   <a href="{{ route('plans.confirm', ['plan' => 'quarterly']) }}" class="w-100 btn btn-lg btn-outline-primary rounded-pill">Escolher Trimestral</a>
                </div>
              </div>
            </div>
            
             <!-- Anual -->
            <div class="col">
              <div class="card mb-4 rounded-3 shadow-sm border-0 h-100">
                <div class="card-header py-3 bg-white border-0">
                  <h4 class="my-0 fw-normal">Anual</h4>
                </div>
                <div class="card-body d-flex flex-column">
                  <h1 class="card-title pricing-card-title">10.000 <small class="text-muted fw-light">Kz</small></h1>
                  <ul class="list-unstyled mt-3 mb-4 flex-grow-1">
                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Candidaturas Automáticas</li>
                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Válido por 1 ano</li>
                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Poupança Máxima</li>
                  </ul>
                   <a href="{{ route('plans.confirm', ['plan' => 'yearly']) }}" class="w-100 btn btn-lg btn-outline-primary rounded-pill">Escolher Anual</a>
                </div>
              </div>
            </div>

        </div>
    </div>
</section>
@endsection

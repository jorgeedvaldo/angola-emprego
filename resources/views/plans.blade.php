@extends('templates.app')

@section('title', 'Planos de Subscrição')

@section('content')
<section class="py-5 bg-light">
    <!-- Cloud Service Explanation Section -->
    <div class="container mb-5">
        <div class="bg-white p-5 rounded-3 shadow-sm border">
            <div class="text-center mb-5">
                <h2 class="fw-bold text-dark">Como Funcionam as Candidaturas Automáticas?</h2>
                <p class="text-muted lead">Deixe que nós tratamos de tudo por si. Veja como é simples:</p>
            </div>

            <div class="row g-4 text-center justify-content-center">
                <div class="col-md-4">
                    <div class="p-4">
                         <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-file-person fs-1"></i>
                        </div>
                        <h5 class="fw-bold">1. Análise de Perfil</h5>
                        <p class="text-muted">Com base no seu CV e preferências, identificamos as vagas que combinam perfeitamente com o seu perfil.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-send-check fs-1"></i>
                        </div>
                        <h5 class="fw-bold">2. Candidatura Automática</h5>
                        <p class="text-muted">Sempre que surgir uma nova vaga na sua área (ex: Motorista, Vendas), nós enviamos a sua candidatura automaticamente.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-clipboard-data fs-1"></i>
                        </div>
                        <h5 class="fw-bold">3. Relatórios Periódicos</h5>
                        <p class="text-muted">Receba periodicamente um relatório detalhado de todas as candidaturas que efectuamos em seu nome.</p>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center mt-4">
                <div class="col-lg-10">
                    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center p-3" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-3 me-3 text-warning"></i>
                        <div>
                            <strong>Nota Importante:</strong> Este serviço garante o envio das suas candidaturas aos nossos clientes, mas <span class="text-decoration-underline">não garante a obtenção de emprego</span>. O recrutamento depende sempre da avaliação da empresa empregadora.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container py-5">
        
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> ERRO! Infelizmente não podemos processar o seu pagamento devido a um erro interno, por favor tente mais tarde.
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

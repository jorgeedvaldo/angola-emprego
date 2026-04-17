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

        <div class="row justify-content-center mb-3 text-center">
            
            <!-- 10 Dias -->
            <div class="col-md-5">
              <div class="card mb-4 rounded-3 shadow border-primary h-100 border-2">
                <div class="card-header py-3 bg-primary text-white border-primary">
                  <h4 class="my-0 fw-normal"><i class="bi bi-star-fill text-warning me-2"></i>10 Dias Premium</h4>
                </div>
                <div class="card-body d-flex flex-column">
                  <h1 class="card-title pricing-card-title">1.000 <small class="text-muted fw-light">Kz</small></h1>
                  <ul class="list-unstyled mt-3 mb-4 flex-grow-1 text-start ps-4">
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i>Candidaturas Automáticas</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i>Válido por 10 dias</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i>Destaque no Perfil</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i>Suporte Prioritário</li>
                  </ul>
                  <a href="{{ route('plans.confirm', ['plan' => '10_days']) }}" class="w-100 btn btn-lg btn-primary rounded-pill fw-bold">Escolher Plano de 10 Dias</a>
                </div>
              </div>
            </div>

        </div>
    </div>
</section>
@endsection

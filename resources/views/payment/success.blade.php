@extends('templates.app')

@section('title', 'Pagamento Confirmado')

@section('content')
<section class="py-5 bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0 shadow-lg rounded-3 text-center">
                    <div class="card-body p-5">
                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                        </div>
                        
                        <h2 class="fw-bold mb-3">Pagamento Confirmado!</h2>
                        <p class="text-muted mb-4 lead">
                            Obrigado, {{ $user->name }}! A sua subscrição <span class="fw-bold text-primary">{{ ucfirst($plan) }}</span> está agora ativa.
                        </p>

                        <div class="alert alert-success bg-opacity-10 border-success mb-4 text-start">
                            <h5 class="alert-heading h6 fw-bold"><i class="bi bi-stars me-2"></i>O que tem acesso agora:</h5>
                            <hr>
                            <ul class="mb-0 small ps-3">
                                <li>Candidaturas ilimitadas a vagas.</li>
                                <li>Destaque para recrutadores.</li>
                                <li>Acesso a cursos exclusivos.</li>
                            </ul>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="{{ route('profile.show') }}" class="btn btn-primary btn-lg rounded-pill fw-bold">
                                Ir para o meu Perfil
                            </a>
                            <a href="{{ route('vagas') }}" class="btn btn-outline-secondary btn-lg rounded-pill fw-bold">
                                Ver Vagas Disponíveis
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

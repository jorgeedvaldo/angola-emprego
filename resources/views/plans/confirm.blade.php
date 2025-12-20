@extends('templates.app')

@section('title', 'Confirmar Subscrição')

@section('content')
<section class="py-5 bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0 shadow-lg rounded-3">
                    <div class="card-header bg-primary text-white py-3">
                        <h4 class="mb-0 fw-bold"><i class="bi bi-shield-check me-2"></i>Verificação de Requisitos</h4>
                    </div>
                    <div class="card-body p-4">
                        <h5 class="mb-4">Para subscrever ao plano <span class="badge bg-primary">{{ ucfirst($plan) }}</span>, precisamos validar alguns dados:</h5>

                        <ul class="list-group list-group-flush mb-4">
                            <!-- CV Check -->
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <h6 class="mb-0 fw-bold">1. CV Carregado</h6>
                                    @if($hasCv)
                                        <small class="text-success">O seu CV está pronto.</small>
                                    @else
                                        <small class="text-danger">Não encontrámos o seu CV.</small>
                                    @endif
                                </div>
                                @if($hasCv)
                                    <i class="bi bi-check-circle-fill text-success fs-4"></i>
                                @else
                                    <a href="{{ route('profile.show') }}" class="btn btn-sm btn-outline-danger">Carregar Agora</a>
                                @endif
                            </li>

                            <!-- Categories Check -->
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <h6 class="mb-0 fw-bold">2. Categorias Selecionadas</h6>
                                    @if($hasCategories)
                                        <small class="text-success">Categorias definidas.</small>
                                    @else
                                        <small class="text-danger">Selecione pelo menos uma categoria.</small>
                                    @endif
                                </div>
                                @if($hasCategories)
                                    <i class="bi bi-check-circle-fill text-success fs-4"></i>
                                @else
                                    <a href="{{ route('profile.show') }}" class="btn btn-sm btn-outline-danger">Selecionar</a>
                                @endif
                            </li>
                        </ul>

                        @if($canSubscribe)
                            <div class="alert alert-success d-flex align-items-center" role="alert">
                                <i class="bi bi-check-circle-fill flex-shrink-0 me-2"></i>
                                <div>
                                    Tudo pronto! Pode prosseguir com o pedido.
                                </div>
                            </div>
                            
                            <form action="{{ route('plans.subscribe') }}" method="POST">
                                @csrf
                                <input type="hidden" name="plan" value="{{ $plan }}">
                                <button type="submit" class="btn btn-primary w-100 btn-lg rounded-pill fw-bold shadow-sm">
                                    Confirmar Pedido <i class="bi bi-arrow-right ms-2"></i>
                                </button>
                            </form>
                        @else
                            <div class="alert alert-warning d-flex align-items-center" role="alert">
                                <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2"></i>
                                <div>
                                    Por favor, complete os requisitos acima no seu perfil antes de continuar.
                                </div>
                            </div>
                            <button class="btn btn-secondary w-100 btn-lg rounded-pill fw-bold" disabled>
                                Confirmar Pedido
                            </button>
                        @endif

                        <div class="text-center mt-3">
                            <a href="{{ route('plans.index') }}" class="text-decoration-none text-muted small">Cancelar e Voltar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

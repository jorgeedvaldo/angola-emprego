@extends('templates.app')

@section('title', 'Meu Perfil Profissional')
@section('description', 'Gerencie o seu CV e preferências de carreira.')

@section('content')
<div class="bg-light py-5">
    <div class="container">
         <h1 class="fw-bold mb-2 text-dark">Meu Perfil</h1>
         <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{url('/')}}">Início</a></li>
                <li class="breadcrumb-item active" aria-current="page">Perfil</li>
            </ol>
        </nav>
    </div>
</div>

<section class="section py-5">
    <div class="container">
        
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-4">
            <!-- Sidebar / User Info -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 text-center p-4">
                    <div class="mb-3">
                         <div class="avatar-placeholder rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center fs-2 fw-bold" style="width: 80px; height: 80px;">
                            {{ substr(Auth::user()->name, 0, 1) }}
                         </div>
                    </div>
                    <h5 class="fw-bold mb-1">{{ Auth::user()->name }}</h5>
                    <p class="text-muted small mb-3">{{ Auth::user()->email }}</p>
                    
                    @if(Auth::user()->cv_path)
                        <div class="d-grid">
                             <a href="{{ Auth::user()->cv_url }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill">
                                <i class="bi bi-file-earmark-pdf me-2"></i> Visualizar Meu CV Atual
                             </a>
                        </div>
                    @else
                        <div class="alert alert-warning small py-2 mb-0">
                            <i class="bi bi-exclamation-triangle me-1"></i> Nenhum CV carregado.
                        </div>
                    @endif
                </div>

                <!-- Subscription Card -->
                <div class="card border-0 shadow-sm rounded-3 text-center p-4 mt-4">
                    <h5 class="fw-bold mb-3">Minha Subscrição</h5>
                    
                    @if(Auth::user()->hasActiveSubscription())
                        <div class="mb-3">
                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fs-6">
                                <i class="bi bi-check-circle-fill me-1"></i> Ativa
                            </span>
                        </div>
                        <h3 class="text-primary fw-bold mb-1">{{ ucfirst(Auth::user()->subscription_plan) }}</h3>
                        <p class="text-muted small mb-3">
                            Válido de {{ Auth::user()->subscription_start->format('d/m/Y') }} 
                            até {{ Auth::user()->subscription_end->format('d/m/Y') }}
                        </p>
                        <div class="alert alert-info small py-2 mb-0">
                            <i class="bi bi-lightning-charge-fill me-1"></i> Candidaturas automáticas ativadas.
                        </div>
                    @elseif(Auth::user()->subscription_status === 'pending')
                         <div class="mb-3">
                            <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill fs-6">
                                <i class="bi bi-hourglass-split me-1"></i> Pendente
                            </span>
                        </div>
                        <div class="alert alert-warning small py-2 mb-0">
                            O seu pedido está em análise. Entraremos em contacto brevemente.
                        </div>
                    @else
                        <div class="mb-3">
                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill fs-6">
                                Inativa
                            </span>
                        </div>
                        <p class="text-muted small mb-3">Subscreva para ativar as candidaturas automáticas e aumentar as suas chances.</p>
                        <a href="{{ route('plans.index') }}" class="btn btn-primary btn-sm rounded-pill w-100 fw-bold">
                            Ver Planos
                        </a>
                    @endif
                </div>
            </div>

            <!-- Profile Form -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title fw-bold mb-0">Editar Preferências e CV</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <h6 class="fw-bold border-bottom pb-2 mb-3">Dados Pessoais</h6>
                                </div>
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-bold">Nome Completo</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="mobile" class="form-label fw-bold">Telefone</label>
                                    <input type="text" class="form-control" id="mobile" name="mobile" value="{{ old('mobile', $user->mobile) }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="sex" class="form-label fw-bold">Gênero</label>
                                    <select class="form-select" id="sex" name="sex">
                                        <option value="" disabled {{ !$user->sex ? 'selected' : '' }}>Selecione</option>
                                        <option value="Masculino" {{ $user->sex === 'Masculino' ? 'selected' : '' }}>Masculino</option>
                                        <option value="Feminino" {{ $user->sex === 'Feminino' ? 'selected' : '' }}>Feminino</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="birth_date" class="form-label fw-bold">Data de Nascimento</label>
                                    <input type="date" class="form-control" id="birth_date" name="birth_date" value="{{ old('birth_date', optional($user->birth_date)->format('Y-m-d')) }}">
                                </div>
                            </div>

                            <div class="mb-4">
                                <h6 class="fw-bold border-bottom pb-2 mb-3">Documentos</h6>
                                <label for="cv" class="form-label fw-bold">Carregar Novo CV (PDF)</label>
                                <input class="form-control" type="file" id="cv" name="cv" accept=".pdf">
                                <div class="form-text">Tamanho máximo: 2MB. Apenas formato PDF.</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold mb-3">Categorias de Interesse</label>
                                <p class="text-muted small">Selecione as categorias para receber recomendações de vagas na página "Vagas Sugeridas".</p>
                                
                                <div class="row row-cols-1 row-cols-md-2 g-2" style="max-height: 300px; overflow-y: auto;">
                                    @foreach($categories as $category)
                                        <div class="col">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $category->id }}" id="cat-{{ $category->id }}"
                                                    {{ $user->categories->contains($category->id) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="cat-{{ $category->id }}">
                                                    {{ $category->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="d-grid d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-primary px-4 rounded-pill">
                                    <i class="bi bi-save me-2"></i> Guardar Alterações
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

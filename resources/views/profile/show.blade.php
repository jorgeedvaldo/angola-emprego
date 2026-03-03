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
                        @if(Auth::user()->avatar)
                            <img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                        @else
                            <div class="avatar-placeholder rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center fs-2 fw-bold" style="width: 80px; height: 80px;">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <h5 class="fw-bold mb-1">{{ Auth::user()->name }}</h5>
                    <p class="text-muted small mb-3">{{ Auth::user()->email }}</p>
                    
                    <div class="mb-3">
                        <a href="{{ route('auth.google') }}" class="btn btn-sm btn-outline-danger rounded-pill" title="Atualize pelo Google usando o mesmo email">
                            <i class="bi bi-google me-1"></i> Sincronizar Avatar
                        </a>
                    </div>
                    
                    @if($user->cvs->count() > 0 || $user->cv_path)
                        <div class="d-grid">
                             <a href="{{ $user->cv_url }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill">
                                <i class="bi bi-file-earmark-pdf me-2"></i> Ver Meu CV Principal
                             </a>
                        </div>
                    @else
                        <div class="alert alert-warning small py-2 mb-0">
                            <i class="bi bi-exclamation-triangle me-1"></i> Nenhum CV carregado.
                        </div>
                    @endif

                    <!-- Public Profile Link -->
                    <div class="mt-4 pt-4 border-top">
                        <h6 class="fw-bold mb-2">O Meu Perfil Público</h6>
                        <p class="small text-muted mb-2">Partilhe o seu perfil com recrutadores:</p>
                        <div class="input-group input-group-sm mb-3">
                            <input type="text" class="form-control bg-light" id="publicProfileLink" value="{{ url('/ae/' . Auth::id()) }}" readonly>
                            <button class="btn btn-outline-primary" type="button" id="copyLinkBtn" onclick="copyPublicLink()" title="Copiar Link">
                                <i class="bi bi-clipboard"></i>
                            </button>
                            <a href="{{ route('profile.public', Auth::id()) }}" target="_blank" class="btn btn-outline-secondary" title="Ver Perfil Público">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url('/ae/' . Auth::id())) }}" target="_blank" class="btn btn-sm text-white" style="background-color: #3b5998;" title="Partilhar no Facebook">
                                <i class="bi bi-facebook"></i> Facebook
                            </a>
                            <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(url('/ae/' . Auth::id())) }}&title={{ urlencode('Perfil Profissional de ' . Auth::user()->name) }}" target="_blank" class="btn btn-sm text-white" style="background-color: #0077b5;" title="Partilhar no LinkedIn">
                                <i class="bi bi-linkedin"></i> LinkedIn
                            </a>
                            <a href="https://api.whatsapp.com/send?text={{ urlencode('Veja o meu perfil profissional: ' . url('/ae/' . Auth::id())) }}" target="_blank" class="btn btn-sm text-white" style="background-color: #25D366;" title="Partilhar no WhatsApp">
                                <i class="bi bi-whatsapp"></i> WhatsApp
                            </a>
                        </div>
                    </div>
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
                    <div class="card-header bg-white py-0 pt-3 border-bottom-0">
                        <ul class="nav nav-tabs card-header-tabs m-0" id="profileTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-bold text-dark px-4" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab" aria-controls="personal" aria-selected="true">
                                    <i class="bi bi-person me-1"></i> Dados Pessoais
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold text-dark px-4" id="cvs-tab" data-bs-toggle="tab" data-bs-target="#cvs" type="button" role="tab" aria-controls="cvs" aria-selected="false">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> Os Meus CVs
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold text-dark px-4" id="preferences-tab" data-bs-toggle="tab" data-bs-target="#preferences" type="button" role="tab" aria-controls="preferences" aria-selected="false">
                                    <i class="bi bi-tags me-1"></i> Preferências
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-4 border rounded-bottom border-top-0">
                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="profile-form">
                            @csrf
                            @method('PUT')

                            <div class="tab-content" id="profileTabsContent">
                                <!-- Personal Data Tab -->
                                <div class="tab-pane fade show active" id="personal" role="tabpanel" aria-labelledby="personal-tab">
                                    <div class="row g-3 mb-2">
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
                                        <div class="col-12 mt-3">
                                            <label for="avatar" class="form-label fw-bold">Foto de Perfil (Opcional)</label>
                                            <input class="form-control" type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/jpg,image/webp">
                                            <div class="form-text">Tamanho máximo: 2MB. Formatos aceites: JPEG, PNG, WEBP. Também pode usar o botão "Sincronizar Avatar" na barra lateral.</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- CVs Tab -->
                                <div class="tab-pane fade" id="cvs" role="tabpanel" aria-labelledby="cvs-tab">
                                    @if($user->cvs->count() > 0)
                                        <div class="table-responsive mb-4 border rounded-3">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Ficheiro</th>
                                                        <th>Data</th>
                                                        <th class="text-end">Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($user->cvs as $cv)
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <i class="bi bi-file-earmark-pdf text-danger fs-4 me-2"></i>
                                                                    <div>
                                                                        <a href="{{ asset('storage/' . $cv->path) }}" target="_blank" class="text-decoration-none text-dark fw-medium d-block">
                                                                            {{ Str::limit($cv->name, 35) }}
                                                                        </a>
                                                                        @if($cv->is_primary)
                                                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2" style="font-size: 0.7rem;">
                                                                                <i class="bi bi-star-fill me-1"></i> Principal
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="text-muted small">
                                                                {{ $cv->created_at->format('d/m/Y') }}
                                                            </td>
                                                            <td class="text-end">
                                                                <div class="d-flex justify-content-end gap-2">
                                                                    @if(!$cv->is_primary)
                                                                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill" onclick="event.preventDefault(); document.getElementById('set-primary-form-{{ $cv->id }}').submit();" title="Definir como Principal">
                                                                            <i class="bi bi-star"></i>
                                                                        </button>
                                                                    @endif
                                                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill" onclick="event.preventDefault(); if(confirm('Tem certeza que deseja eliminar este CV?')) document.getElementById('delete-cv-form-{{ $cv->id }}').submit();" title="Eliminar CV">
                                                                        <i class="bi bi-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif

                                    <label for="cv" class="form-label fw-bold mt-2">Carregar Novo CV (PDF)</label>
                                    <input class="form-control" type="file" id="cv" name="cv" accept=".pdf">
                                    <div class="form-text">Tamanho máximo: 2MB. Apenas formato PDF. Pode manter múltiplos CVs e escolher o principal.</div>
                                </div>

                                <!-- Preferences Tab -->
                                <div class="tab-pane fade" id="preferences" role="tabpanel" aria-labelledby="preferences-tab">
                                    <p class="text-muted small mb-3">Selecione as categorias para receber recomendações de vagas na página "Vagas Sugeridas".</p>
                                    
                                    <div class="row row-cols-1 row-cols-md-2 g-2 mt-2" style="max-height: 300px; overflow-y: auto;">
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
                            </div>

                            <div class="d-grid d-md-flex justify-content-md-end mt-4 pt-4 border-top">
                                <button type="submit" class="btn btn-primary px-4 rounded-pill">
                                    <i class="bi bi-save me-2"></i> Guardar Alterações
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Completed Courses Section -->
                @if($user->completed_courses->count() > 0)
                <div class="card border-0 shadow-sm rounded-3 mt-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title fw-bold mb-0">Cursos Concluídos & Certificados</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Curso</th>
                                        <th>Concluído em</th>
                                        <th class="text-end">Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->completed_courses as $course)
                                        <tr>
                                            <td class="fw-bold">{{ $course->title }}</td>
                                            <td>
                                                @php
                                                    $lastLesson = Auth::user()->lessons()
                                                        ->whereIn('lesson_id', $course->lessons->pluck('id'))
                                                        ->whereNotNull('lesson_user.completed_at')
                                                        ->orderBy('lesson_user.completed_at', 'desc')
                                                        ->first();
                                                @endphp
                                                {{ $lastLesson ? \Carbon\Carbon::parse($lastLesson->pivot->completed_at)->format('d/m/Y') : 'Data Indisponível' }}
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('certificates.verify', ['user' => $user->id, 'course' => $course->slug]) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                                    <i class="bi bi-award me-1"></i> Ver Certificado
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
</section>

<!-- Nested Forms Extracted Outside -->
@foreach($user->cvs as $cv)
    @if(!$cv->is_primary)
    <form id="set-primary-form-{{ $cv->id }}" action="{{ route('profile.cv.primary', $cv->id) }}" method="POST" class="d-none">
        @csrf
    </form>
    @endif
    <form id="delete-cv-form-{{ $cv->id }}" action="{{ route('profile.cv.delete', $cv->id) }}" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>
@endforeach

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Restore active tab from session storage
        const activeTab = sessionStorage.getItem('profileActiveTab');
        if (activeTab) {
            const tabButton = document.querySelector(`button[data-bs-target="${activeTab}"]`);
            if (tabButton) {
                // Remove active class from all
                document.querySelectorAll('#profileTabs .nav-link').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('#profileTabsContent .tab-pane').forEach(pane => {
                    pane.classList.remove('show', 'active');
                });
                
                // Add active class to saved
                tabButton.classList.add('active');
                document.querySelector(activeTab).classList.add('show', 'active');
            }
        }

        // Save active tab to session storage on click
        document.querySelectorAll('#profileTabs .nav-link').forEach(button => {
            button.addEventListener('click', function() {
                sessionStorage.setItem('profileActiveTab', this.getAttribute('data-bs-target'));
            });
        });
    });

    function copyPublicLink() {
        var copyText = document.getElementById("publicProfileLink");
        copyText.select();
        copyText.setSelectionRange(0, 99999); // For mobile devices
        
        navigator.clipboard.writeText(copyText.value).then(function() {
            var btn = document.getElementById('copyLinkBtn');
            var originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2"></i>';
            btn.classList.add('btn-success');
            btn.classList.remove('btn-outline-primary');
            
            setTimeout(function() {
                btn.innerHTML = originalHtml;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-primary');
            }, 2000);
        });
    }
</script>
@endsection

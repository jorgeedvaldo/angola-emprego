@extends('templates.app')

@section('title', 'Meu Perfil Profissional')
@section('description', 'Gerencie o seu CV e preferências de carreira.')

@section('content')
{{-- Profile Hero --}}
<div class="profile-mgmt-hero">
    <div class="profile-mgmt-hero-pattern"></div>
    <div class="container position-relative">
        <div class="row align-items-end">
            <div class="col-auto">
                <div class="profile-mgmt-avatar-wrap">
                    @if(Auth::user()->avatar)
                        <img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}" class="profile-mgmt-avatar">
                    @else
                        <div class="profile-mgmt-avatar profile-mgmt-avatar-char">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                    @endif
                    <a href="{{ route('auth.google') }}" class="profile-mgmt-avatar-sync" title="Sincronizar Avatar via Google">
                        <i class="bi bi-google"></i>
                    </a>
                </div>
            </div>
            <div class="col">
                <h1 class="profile-mgmt-name">{{ Auth::user()->name }}</h1>
                <div class="d-flex align-items-center gap-3 mb-1">
                    <span class="profile-mgmt-username"><i class="bi bi-at"></i>{{ Auth::user()->username }}</span>
                    <span class="profile-mgmt-email"><i class="bi bi-envelope me-1"></i>{{ Auth::user()->email }}</span>
                </div>
            </div>
            <div class="col-auto d-none d-md-flex align-items-end gap-2 pb-2">
                <a href="{{ route('profile.public', Auth::user()->username) }}" target="_blank" class="btn btn-sm btn-light rounded-pill px-3 shadow-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver Perfil Público
                </a>
                <button type="button" class="btn btn-sm btn-light rounded-pill px-3 shadow-sm" onclick="copyPublicLink()">
                    <i class="bi bi-clipboard me-1"></i> Copiar Link
                </button>
                <input type="hidden" id="publicProfileLink" value="{{ route('profile.public', Auth::user()->username) }}">
            </div>
        </div>
    </div>
</div>

<section class="py-4">
    <div class="container">
        
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert" style="background: linear-gradient(135deg, #dcfce7, #d1fae5); border-left: 4px solid #22c55e !important;">
                <i class="bi bi-check-circle-fill text-success me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Navigation Pills --}}
        <div class="profile-mgmt-nav mb-4">
            <ul class="nav nav-pills" id="profileTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab" aria-controls="personal" aria-selected="true">
                        <i class="bi bi-person"></i> <span>Dados Pessoais</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="cvs-tab" data-bs-toggle="tab" data-bs-target="#cvs" type="button" role="tab" aria-controls="cvs" aria-selected="false">
                        <i class="bi bi-file-earmark-pdf"></i> <span>Currículos</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="preferences-tab" data-bs-toggle="tab" data-bs-target="#preferences" type="button" role="tab" aria-controls="preferences" aria-selected="false">
                        <i class="bi bi-tags"></i> <span>Preferências</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="extended-tab" data-bs-toggle="tab" data-bs-target="#extended" type="button" role="tab" aria-controls="extended" aria-selected="false">
                        <i class="bi bi-person-vcard"></i> <span>Perfil Público</span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="row g-4">
            {{-- Main Content --}}
            <div class="col-lg-8">
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="profile-form">
                    @csrf
                    @method('PUT')

                    <div class="tab-content" id="profileTabsContent">
                        
                        {{-- ═══ DADOS PESSOAIS ═══ --}}
                        <div class="tab-pane fade show active" id="personal" role="tabpanel" aria-labelledby="personal-tab">
                            <div class="mgmt-card">
                                <div class="mgmt-card-header">
                                    <div class="mgmt-card-icon bg-primary-subtle text-primary">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                    <div>
                                        <h5 class="mgmt-card-title">Informações Pessoais</h5>
                                        <p class="mgmt-card-subtitle">Atualize os seus dados pessoais</p>
                                    </div>
                                </div>
                                <div class="mgmt-card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="name" class="mgmt-label">Nome Completo</label>
                                            <input type="text" class="form-control mgmt-input" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="mobile" class="mgmt-label">Telefone</label>
                                            <div class="input-group">
                                                <span class="input-group-text mgmt-input-icon"><i class="bi bi-telephone"></i></span>
                                                <input type="text" class="form-control mgmt-input" id="mobile" name="mobile" value="{{ old('mobile', $user->mobile) }}" placeholder="+244 9XX XXX XXX">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="sex" class="mgmt-label">Gênero</label>
                                            <select class="form-select mgmt-input" id="sex" name="sex">
                                                <option value="" disabled {{ !$user->sex ? 'selected' : '' }}>Selecione</option>
                                                <option value="Masculino" {{ $user->sex === 'Masculino' ? 'selected' : '' }}>Masculino</option>
                                                <option value="Feminino" {{ $user->sex === 'Feminino' ? 'selected' : '' }}>Feminino</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="birth_date" class="mgmt-label">Data de Nascimento</label>
                                            <input type="date" class="form-control mgmt-input" id="birth_date" name="birth_date" value="{{ old('birth_date', optional($user->birth_date)->format('Y-m-d')) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mgmt-card mt-4">
                                <div class="mgmt-card-header">
                                    <div class="mgmt-card-icon bg-warning-subtle text-warning">
                                        <i class="bi bi-camera-fill"></i>
                                    </div>
                                    <div>
                                        <h5 class="mgmt-card-title">Foto de Perfil</h5>
                                        <p class="mgmt-card-subtitle">Carregue uma foto ou sincronize via Google</p>
                                    </div>
                                </div>
                                <div class="mgmt-card-body">
                                    <div class="avatar-upload-zone">
                                        <div class="avatar-upload-preview">
                                            @if(Auth::user()->avatar)
                                                <img src="{{ Auth::user()->avatar }}" alt="Avatar" id="avatar-preview-img">
                                            @else
                                                <div class="avatar-upload-placeholder" id="avatar-preview-placeholder">
                                                    <i class="bi bi-cloud-arrow-up"></i>
                                                    <span>Carregar foto</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="avatar-upload-info">
                                            <input class="form-control mgmt-input" type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/jpg,image/webp">
                                            <div class="form-text mt-2">
                                                <i class="bi bi-info-circle me-1"></i> Máximo 2MB · JPEG, PNG, WEBP · Redimensionado para 128×128px
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill mgmt-save-btn">
                                    <i class="bi bi-check-lg me-2"></i> Guardar Alterações
                                </button>
                            </div>
                        </div>

                        {{-- ═══ CURRÍCULOS ═══ --}}
                        <div class="tab-pane fade" id="cvs" role="tabpanel" aria-labelledby="cvs-tab">
                            <div class="mgmt-card">
                                <div class="mgmt-card-header">
                                    <div class="mgmt-card-icon bg-danger-subtle text-danger">
                                        <i class="bi bi-file-earmark-pdf-fill"></i>
                                    </div>
                                    <div>
                                        <h5 class="mgmt-card-title">Os Meus Currículos</h5>
                                        <p class="mgmt-card-subtitle">Faça upload e gerencie os seus CVs</p>
                                    </div>
                                </div>
                                <div class="mgmt-card-body">
                                    @if($user->cvs->count() > 0)
                                        <div class="cv-list mb-4">
                                            @foreach($user->cvs as $cv)
                                                <div class="cv-list-item {{ $cv->is_primary ? 'cv-primary' : '' }}">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="cv-list-icon">
                                                            <i class="bi bi-filetype-pdf"></i>
                                                        </div>
                                                        <div class="flex-grow-1 overflow-hidden">
                                                            <a href="{{ asset('storage/' . $cv->path) }}" target="_blank" class="cv-list-name">
                                                                {{ Str::limit($cv->name, 40) }}
                                                            </a>
                                                            <div class="d-flex align-items-center gap-2 mt-1">
                                                                <small class="text-muted">{{ $cv->created_at->format('d/m/Y') }}</small>
                                                                @if($cv->is_primary)
                                                                    <span class="cv-primary-badge">
                                                                        <i class="bi bi-star-fill me-1"></i> Principal
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="d-flex gap-1">
                                                            @if(!$cv->is_primary)
                                                                <button type="button" class="btn btn-sm btn-light rounded-pill cv-action-btn" 
                                                                    onclick="event.preventDefault(); document.getElementById('set-primary-form-{{ $cv->id }}').submit();" 
                                                                    title="Definir como Principal">
                                                                    <i class="bi bi-star text-warning"></i>
                                                                </button>
                                                            @endif
                                                            <a href="{{ asset('storage/' . $cv->path) }}" target="_blank" class="btn btn-sm btn-light rounded-pill cv-action-btn" title="Visualizar">
                                                                <i class="bi bi-eye text-primary"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-light rounded-pill cv-action-btn" 
                                                                onclick="event.preventDefault(); if(confirm('Tem certeza que deseja eliminar este CV?')) document.getElementById('delete-cv-form-{{ $cv->id }}').submit();" 
                                                                title="Eliminar">
                                                                <i class="bi bi-trash text-danger"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="empty-state mb-4">
                                            <i class="bi bi-file-earmark-plus"></i>
                                            <p>Nenhum CV carregado. Faça upload do seu primeiro currículo.</p>
                                        </div>
                                    @endif

                                    <div class="upload-zone">
                                        <i class="bi bi-cloud-arrow-up"></i>
                                        <p class="mb-1 fw-medium">Carregar Novo CV</p>
                                        <p class="text-muted small mb-2">Arraste o ficheiro ou clique para selecionar</p>
                                        <input class="form-control mgmt-input" type="file" id="cv" name="cv" accept=".pdf" style="max-width: 400px;">
                                        <div class="form-text mt-2">
                                            <i class="bi bi-info-circle me-1"></i> Apenas PDF · Máximo 2MB
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill mgmt-save-btn">
                                    <i class="bi bi-check-lg me-2"></i> Guardar Alterações
                                </button>
                            </div>
                        </div>

                        {{-- ═══ PREFERÊNCIAS ═══ --}}
                        <div class="tab-pane fade" id="preferences" role="tabpanel" aria-labelledby="preferences-tab">
                            <div class="mgmt-card">
                                <div class="mgmt-card-header">
                                    <div class="mgmt-card-icon bg-info-subtle text-info">
                                        <i class="bi bi-tags-fill"></i>
                                    </div>
                                    <div>
                                        <h5 class="mgmt-card-title">Áreas de Interesse</h5>
                                        <p class="mgmt-card-subtitle">Selecione categorias para receber recomendações de vagas</p>
                                    </div>
                                </div>
                                <div class="mgmt-card-body">
                                    <div class="categories-grid">
                                        @foreach($categories as $category)
                                            <label class="category-check-item" for="cat-{{ $category->id }}">
                                                <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $category->id }}" id="cat-{{ $category->id }}"
                                                    {{ $user->categories->contains($category->id) ? 'checked' : '' }}>
                                                <span class="category-check-label">{{ $category->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill mgmt-save-btn">
                                    <i class="bi bi-check-lg me-2"></i> Guardar Alterações
                                </button>
                            </div>
                        </div>

                        {{-- ═══ PERFIL PÚBLICO ═══ --}}
                        <div class="tab-pane fade" id="extended" role="tabpanel" aria-labelledby="extended-tab">
                            <p class="text-muted small mb-4">
                                <i class="bi bi-info-circle me-1"></i> Estas informações são exibidas no seu perfil público para recrutadores e empregadores.
                            </p>

                            {{-- Sobre Mim --}}
                            <div class="mgmt-card mb-4">
                                <div class="mgmt-card-header">
                                    <div class="mgmt-card-icon bg-primary-subtle text-primary">
                                        <i class="bi bi-chat-quote-fill"></i>
                                    </div>
                                    <div>
                                        <h5 class="mgmt-card-title">Sobre Mim</h5>
                                        <p class="mgmt-card-subtitle">Uma breve descrição sobre si</p>
                                    </div>
                                </div>
                                <div class="mgmt-card-body">
                                    <textarea class="form-control mgmt-input" id="bio-textarea" rows="3" maxlength="1000" form="bio-form" name="bio" placeholder="Sou um profissional dedicado com experiência em...">{{ old('bio', $user->bio) }}</textarea>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <div class="form-text"><span id="bio-count">{{ strlen($user->bio ?? '') }}</span>/1000 caracteres</div>
                                        <button type="submit" form="bio-form" class="btn btn-sm btn-primary rounded-pill px-3">
                                            <i class="bi bi-save me-1"></i> Guardar
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Habilidades --}}
                            <div class="mgmt-card mb-4">
                                <div class="mgmt-card-header">
                                    <div class="mgmt-card-icon bg-warning-subtle text-warning">
                                        <i class="bi bi-lightning-fill"></i>
                                    </div>
                                    <div>
                                        <h5 class="mgmt-card-title">Habilidades</h5>
                                        <p class="mgmt-card-subtitle">Competências técnicas e interpessoais</p>
                                    </div>
                                </div>
                                <div class="mgmt-card-body">
                                    @if($user->skills->count() > 0)
                                        <div class="d-flex flex-wrap gap-2 mb-3">
                                            @foreach($user->skills as $skill)
                                                <span class="skill-tag">
                                                    {{ $skill->name }}
                                                    <button type="button" class="skill-tag-remove" 
                                                        onclick="event.preventDefault(); if(confirm('Remover?')) document.getElementById('delete-skill-{{ $skill->id }}').submit();">
                                                        <i class="bi bi-x"></i>
                                                    </button>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                    <div class="input-group" style="max-width: 420px;">
                                        <input type="text" class="form-control mgmt-input" name="name" form="skill-form" placeholder="Ex: Laravel, Excel, Design Gráfico..." required>
                                        <button type="submit" form="skill-form" class="btn btn-outline-primary rounded-end-pill px-3">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Formação Académica --}}
                            <div class="mgmt-card mb-4">
                                <div class="mgmt-card-header">
                                    <div class="mgmt-card-icon bg-info-subtle text-info">
                                        <i class="bi bi-mortarboard-fill"></i>
                                    </div>
                                    <div>
                                        <h5 class="mgmt-card-title">Formação Académica</h5>
                                        <p class="mgmt-card-subtitle">Escolas e universidades onde estudou</p>
                                    </div>
                                </div>
                                <div class="mgmt-card-body">
                                    @if($user->educations->count() > 0)
                                        <div class="entry-list mb-3">
                                            @foreach($user->educations as $edu)
                                                <div class="entry-list-item">
                                                    <div class="entry-list-marker"></div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="fw-bold mb-0">{{ $edu->institution }}</h6>
                                                        @if($edu->degree || $edu->field_of_study)
                                                            <span class="text-primary small fw-medium">{{ $edu->degree }}{{ $edu->degree && $edu->field_of_study ? ' · ' : '' }}{{ $edu->field_of_study }}</span>
                                                        @endif
                                                        @if($edu->start_year || $edu->end_year)
                                                            <br><small class="text-muted"><i class="bi bi-calendar3 me-1"></i>{{ $edu->start_year ?? '—' }} — {{ $edu->end_year ?? 'Presente' }}</small>
                                                        @endif
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-light rounded-circle entry-delete" 
                                                        onclick="event.preventDefault(); if(confirm('Remover esta formação?')) document.getElementById('delete-education-{{ $edu->id }}').submit();">
                                                        <i class="bi bi-trash text-danger"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    <div class="add-entry-form">
                                        <div class="row g-2">
                                            <div class="col-12">
                                                <input type="text" class="form-control form-control-sm mgmt-input" name="institution" form="education-form" placeholder="Instituição *" required>
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" class="form-control form-control-sm mgmt-input" name="degree" form="education-form" placeholder="Grau (ex: Licenciatura)">
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" class="form-control form-control-sm mgmt-input" name="field_of_study" form="education-form" placeholder="Área de Estudo">
                                            </div>
                                            <div class="col-6">
                                                <input type="text" class="form-control form-control-sm mgmt-input" name="start_year" form="education-form" placeholder="Ano Início" maxlength="4">
                                            </div>
                                            <div class="col-6">
                                                <input type="text" class="form-control form-control-sm mgmt-input" name="end_year" form="education-form" placeholder="Ano Fim" maxlength="4">
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" form="education-form" class="btn btn-sm btn-outline-primary rounded-pill"><i class="bi bi-plus-lg me-1"></i> Adicionar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Experiência Profissional --}}
                            <div class="mgmt-card mb-4">
                                <div class="mgmt-card-header">
                                    <div class="mgmt-card-icon bg-success-subtle text-success">
                                        <i class="bi bi-briefcase-fill"></i>
                                    </div>
                                    <div>
                                        <h5 class="mgmt-card-title">Experiência Profissional</h5>
                                        <p class="mgmt-card-subtitle">Historial de emprego e projectos</p>
                                    </div>
                                </div>
                                <div class="mgmt-card-body">
                                    @if($user->experiences->count() > 0)
                                        <div class="entry-list mb-3">
                                            @foreach($user->experiences as $exp)
                                                <div class="entry-list-item">
                                                    <div class="entry-list-marker bg-success"></div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="fw-bold mb-0">{{ $exp->position }}</h6>
                                                        <span class="text-success small fw-medium">{{ $exp->company }}</span>
                                                        <br><small class="text-muted">
                                                            <i class="bi bi-calendar3 me-1"></i>
                                                            {{ $exp->start_date ? $exp->start_date->format('m/Y') : '—' }} — {{ $exp->end_date ? $exp->end_date->format('m/Y') : 'Presente' }}
                                                        </small>
                                                        @if($exp->description)
                                                            <p class="text-muted small mt-1 mb-0">{{ \Illuminate\Support\Str::limit($exp->description, 120) }}</p>
                                                        @endif
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-light rounded-circle entry-delete" 
                                                        onclick="event.preventDefault(); if(confirm('Remover esta experiência?')) document.getElementById('delete-experience-{{ $exp->id }}').submit();">
                                                        <i class="bi bi-trash text-danger"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    <div class="add-entry-form">
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <input type="text" class="form-control form-control-sm mgmt-input" name="company" form="experience-form" placeholder="Empresa *" required>
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" class="form-control form-control-sm mgmt-input" name="position" form="experience-form" placeholder="Cargo *" required>
                                            </div>
                                            <div class="col-12">
                                                <textarea class="form-control form-control-sm mgmt-input" name="description" form="experience-form" rows="2" placeholder="Descrição das responsabilidades (opcional)"></textarea>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small text-muted mb-1">Início</label>
                                                <input type="date" class="form-control form-control-sm mgmt-input" name="start_date" form="experience-form">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small text-muted mb-1">Fim <span class="text-muted">(vazio = actual)</span></label>
                                                <input type="date" class="form-control form-control-sm mgmt-input" name="end_date" form="experience-form">
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" form="experience-form" class="btn btn-sm btn-outline-primary rounded-pill"><i class="bi bi-plus-lg me-1"></i> Adicionar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Idiomas --}}
                            <div class="mgmt-card mb-4">
                                <div class="mgmt-card-header">
                                    <div class="mgmt-card-icon bg-danger-subtle text-danger">
                                        <i class="bi bi-translate"></i>
                                    </div>
                                    <div>
                                        <h5 class="mgmt-card-title">Idiomas</h5>
                                        <p class="mgmt-card-subtitle">Línguas que domina</p>
                                    </div>
                                </div>
                                <div class="mgmt-card-body">
                                    @if($user->languages->count() > 0)
                                        <div class="entry-list mb-3">
                                            @foreach($user->languages as $lang)
                                                <div class="entry-list-item py-2">
                                                    <div class="flex-grow-1 d-flex align-items-center gap-3">
                                                        <span class="fw-bold">{{ $lang->language }}</span>
                                                        <span class="language-level-badge level-{{ \Illuminate\Support\Str::slug($lang->level) }}">{{ $lang->level }}</span>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-light rounded-circle entry-delete" 
                                                        onclick="event.preventDefault(); if(confirm('Remover?')) document.getElementById('delete-language-{{ $lang->id }}').submit();">
                                                        <i class="bi bi-trash text-danger"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    <div class="add-entry-form">
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <input type="text" class="form-control form-control-sm mgmt-input" name="language" form="language-form" placeholder="Ex: Português, Inglês..." required>
                                            </div>
                                            <div class="col-md-6">
                                                <select class="form-select form-select-sm mgmt-input" name="level" form="language-form" required>
                                                    <option value="" disabled selected>Nível *</option>
                                                    <option value="Básico">Básico</option>
                                                    <option value="Intermediário">Intermediário</option>
                                                    <option value="Avançado">Avançado</option>
                                                    <option value="Fluente">Fluente</option>
                                                    <option value="Nativo">Nativo</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" form="language-form" class="btn btn-sm btn-outline-primary rounded-pill"><i class="bi bi-plus-lg me-1"></i> Adicionar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </form>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                {{-- Quick Actions --}}
                <div class="mgmt-card mb-4 d-md-none">
                    <div class="mgmt-card-body text-center py-3">
                        <a href="{{ route('profile.public', Auth::user()->username) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 me-2">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Ver Perfil
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="copyPublicLink()">
                            <i class="bi bi-clipboard me-1"></i> Copiar Link
                        </button>
                    </div>
                </div>

                {{-- Public Profile Share --}}
                <div class="mgmt-card mb-4">
                    <div class="mgmt-card-body">
                        <h6 class="fw-bold mb-3"><i class="bi bi-share me-2 text-primary"></i>Partilhar Perfil</h6>
                        <div class="share-link-box mb-3">
                            <span class="share-link-url">{{ route('profile.public', Auth::user()->username) }}</span>
                            <button type="button" class="share-link-copy" id="copyLinkBtn" onclick="copyPublicLink()" title="Copiar">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('profile.public', Auth::user()->username)) }}" target="_blank" class="btn btn-sm share-social-btn share-fb flex-fill">
                                <i class="bi bi-facebook me-1"></i> Facebook
                            </a>
                            <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(route('profile.public', Auth::user()->username)) }}&title={{ urlencode('Perfil Profissional de ' . Auth::user()->name) }}" target="_blank" class="btn btn-sm share-social-btn share-li flex-fill">
                                <i class="bi bi-linkedin me-1"></i> LinkedIn
                            </a>
                            <a href="https://api.whatsapp.com/send?text={{ urlencode('Veja o meu perfil profissional: ' . route('profile.public', Auth::user()->username)) }}" target="_blank" class="btn btn-sm share-social-btn share-wa flex-fill">
                                <i class="bi bi-whatsapp me-1"></i> WhatsApp
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Subscription Card --}}
                <div class="mgmt-card mb-4">
                    <div class="mgmt-card-body">
                        <h6 class="fw-bold mb-3"><i class="bi bi-rocket-takeoff me-2 text-primary"></i>Subscrição</h6>
                        @if(Auth::user()->hasActiveSubscription())
                            <div class="subscription-status active">
                                <div class="subscription-status-icon bg-success">
                                    <i class="bi bi-check-lg text-white"></i>
                                </div>
                                <div>
                                    <span class="fw-bold text-success">Activa</span>
                                    <span class="d-block small text-muted">{{ ucfirst(Auth::user()->subscription_plan) }}</span>
                                </div>
                            </div>
                            <p class="text-muted small mt-2 mb-0">
                                Válida até {{ Auth::user()->subscription_end->format('d/m/Y') }}
                            </p>
                        @elseif(Auth::user()->subscription_status === 'pending')
                            <div class="subscription-status pending">
                                <div class="subscription-status-icon bg-warning">
                                    <i class="bi bi-hourglass-split text-white"></i>
                                </div>
                                <div>
                                    <span class="fw-bold text-warning">Pendente</span>
                                    <span class="d-block small text-muted">Em análise</span>
                                </div>
                            </div>
                        @else
                            <div class="subscription-status inactive">
                                <div class="subscription-status-icon bg-secondary">
                                    <i class="bi bi-x-lg text-white"></i>
                                </div>
                                <div>
                                    <span class="fw-bold text-secondary">Inactiva</span>
                                    <span class="d-block small text-muted">Sem plano activo</span>
                                </div>
                            </div>
                            <a href="{{ route('plans.index') }}" class="btn btn-primary btn-sm rounded-pill w-100 mt-3">
                                <i class="bi bi-lightning-charge-fill me-1"></i> Ver Planos
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Completed Courses --}}
                @if($user->completed_courses->count() > 0)
                <div class="mgmt-card mb-4">
                    <div class="mgmt-card-body">
                        <h6 class="fw-bold mb-3"><i class="bi bi-mortarboard me-2 text-primary"></i>Cursos Concluídos</h6>
                        @foreach($user->completed_courses as $course)
                            <div class="course-mini-item">
                                <div class="course-mini-icon">
                                    <i class="bi bi-journal-check"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="fw-medium small">{{ $course->title }}</span>
                                </div>
                                <a href="{{ route('certificates.verify', ['user' => $user->id, 'course' => $course->slug]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0" target="_blank">
                                    <small><i class="bi bi-award"></i></small>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- CV Quick Access --}}
                @if($user->cvs->count() > 0 || $user->cv_path)
                <div class="mgmt-card">
                    <div class="mgmt-card-body">
                        <h6 class="fw-bold mb-3"><i class="bi bi-file-earmark-pdf me-2 text-danger"></i>CV Principal</h6>
                        <a href="{{ $user->cv_url }}" target="_blank" class="btn btn-outline-danger btn-sm rounded-pill w-100">
                            <i class="bi bi-eye me-1"></i> Visualizar CV
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- Hidden Forms --}}
<form id="bio-form" action="{{ route('profile.bio.update') }}" method="POST" class="d-none">@csrf @method('PUT')</form>
<form id="skill-form" action="{{ route('profile.skills.store') }}" method="POST" class="d-none">@csrf</form>
<form id="education-form" action="{{ route('profile.education.store') }}" method="POST" class="d-none">@csrf</form>
<form id="experience-form" action="{{ route('profile.experience.store') }}" method="POST" class="d-none">@csrf</form>
<form id="language-form" action="{{ route('profile.languages.store') }}" method="POST" class="d-none">@csrf</form>

@foreach($user->skills as $skill)
<form id="delete-skill-{{ $skill->id }}" action="{{ route('profile.skills.delete', $skill->id) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
@endforeach
@foreach($user->educations as $edu)
<form id="delete-education-{{ $edu->id }}" action="{{ route('profile.education.delete', $edu->id) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
@endforeach
@foreach($user->experiences as $exp)
<form id="delete-experience-{{ $exp->id }}" action="{{ route('profile.experience.delete', $exp->id) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
@endforeach
@foreach($user->languages as $lang)
<form id="delete-language-{{ $lang->id }}" action="{{ route('profile.languages.delete', $lang->id) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
@endforeach
@foreach($user->cvs as $cv)
    @if(!$cv->is_primary)
    <form id="set-primary-form-{{ $cv->id }}" action="{{ route('profile.cv.primary', $cv->id) }}" method="POST" class="d-none">@csrf</form>
    @endif
    <form id="delete-cv-form-{{ $cv->id }}" action="{{ route('profile.cv.delete', $cv->id) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
@endforeach

<style>
    /* ═══════════════════════════════════ */
    /* PROFILE MANAGEMENT — REDESIGN      */
    /* ═══════════════════════════════════ */

    /* Hero */
    .profile-mgmt-hero {
        position: relative;
        background: linear-gradient(135deg, #1e3a5f 0%, #2557a7 40%, #4f73c9 100%);
        padding: 40px 0 30px;
        overflow: hidden;
    }
    .profile-mgmt-hero-pattern {
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    .profile-mgmt-avatar-wrap {
        position: relative;
    }
    .profile-mgmt-avatar {
        width: 90px;
        height: 90px;
        border-radius: 16px;
        object-fit: cover;
        border: 3px solid rgba(255,255,255,0.3);
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }
    .profile-mgmt-avatar-char {
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,0.15);
        color: #fff;
        font-size: 2rem;
        font-weight: 700;
        backdrop-filter: blur(10px);
    }
    .profile-mgmt-avatar-sync {
        position: absolute;
        bottom: -4px;
        right: -4px;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #fff;
        color: #ea4335;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        transition: transform 0.2s;
    }
    .profile-mgmt-avatar-sync:hover {
        transform: scale(1.1);
        color: #ea4335;
    }
    .profile-mgmt-name {
        color: #fff;
        font-size: 1.6rem;
        font-weight: 700;
        margin-bottom: 2px;
    }
    .profile-mgmt-username {
        color: rgba(255,255,255,0.7);
        font-size: 0.9rem;
    }
    .profile-mgmt-email {
        color: rgba(255,255,255,0.5);
        font-size: 0.85rem;
    }

    /* Navigation Pills */
    .profile-mgmt-nav {
        background: #fff;
        border-radius: 12px;
        padding: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        border: 1px solid #eee;
        overflow-x: auto;
    }
    .profile-mgmt-nav .nav-pills {
        flex-wrap: nowrap;
        gap: 4px;
    }
    .profile-mgmt-nav .nav-link {
        color: #6b7280;
        font-weight: 600;
        font-size: 0.875rem;
        border-radius: 8px;
        padding: 10px 16px;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .profile-mgmt-nav .nav-link:hover {
        color: #2557a7;
        background: #f0f4ff;
    }
    .profile-mgmt-nav .nav-link.active {
        background: #2557a7;
        color: #fff;
        box-shadow: 0 2px 8px rgba(37, 87, 167, 0.3);
    }
    .profile-mgmt-nav .nav-link i {
        font-size: 1rem;
    }

    /* Cards */
    .mgmt-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e8e8e8;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .mgmt-card-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 20px 24px 0;
    }
    .mgmt-card-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    .mgmt-card-title {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 0;
        color: #1a1a2e;
    }
    .mgmt-card-subtitle {
        font-size: 0.8rem;
        color: #9ca3af;
        margin-bottom: 0;
    }
    .mgmt-card-body {
        padding: 16px 24px 24px;
    }

    /* Inputs */
    .mgmt-label {
        font-weight: 600;
        font-size: 0.85rem;
        color: #374151;
        margin-bottom: 6px;
        display: block;
    }
    .mgmt-input {
        border-radius: 8px !important;
        border-color: #e5e7eb !important;
        padding: 10px 14px !important;
        font-size: 0.9rem !important;
        transition: border-color 0.2s, box-shadow 0.2s !important;
    }
    .mgmt-input:focus {
        border-color: #2557a7 !important;
        box-shadow: 0 0 0 3px rgba(37, 87, 167, 0.1) !important;
    }
    .mgmt-input-icon {
        background: #f9fafb;
        border-color: #e5e7eb;
        border-radius: 8px 0 0 8px !important;
        color: #9ca3af;
    }

    /* Save button */
    .mgmt-save-btn {
        font-weight: 600;
        letter-spacing: 0.02em;
        box-shadow: 0 2px 8px rgba(37, 87, 167, 0.3);
        transition: all 0.2s;
    }
    .mgmt-save-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 87, 167, 0.4);
    }

    /* Avatar upload */
    .avatar-upload-zone {
        display: flex;
        align-items: center;
        gap: 20px;
    }
    .avatar-upload-preview {
        width: 80px;
        height: 80px;
        border-radius: 12px;
        overflow: hidden;
        border: 2px dashed #d1d5db;
        flex-shrink: 0;
    }
    .avatar-upload-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .avatar-upload-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-size: 0.65rem;
        gap: 2px;
    }
    .avatar-upload-placeholder i {
        font-size: 1.4rem;
    }

    /* CV List */
    .cv-list-item {
        padding: 14px 16px;
        border-radius: 10px;
        border: 1px solid #e8e8e8;
        margin-bottom: 8px;
        transition: all 0.2s;
    }
    .cv-list-item:hover {
        border-color: #c7d2fe;
        background: #fafbff;
    }
    .cv-list-item.cv-primary {
        border-color: #bbf7d0;
        background: #f0fdf4;
    }
    .cv-list-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #dc2626;
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    .cv-list-name {
        color: #1f2937;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
    }
    .cv-list-name:hover { color: #2557a7; }
    .cv-primary-badge {
        font-size: 0.65rem;
        font-weight: 600;
        background: #dcfce7;
        color: #166534;
        padding: 2px 8px;
        border-radius: 50px;
    }
    .cv-action-btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e8e8e8;
    }

    /* Upload zone */
    .upload-zone {
        text-align: center;
        padding: 24px;
        border: 2px dashed #d1d5db;
        border-radius: 12px;
        background: #fafbfc;
        transition: border-color 0.2s;
    }
    .upload-zone:hover {
        border-color: #2557a7;
    }
    .upload-zone > i {
        font-size: 2rem;
        color: #9ca3af;
    }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 24px;
        color: #9ca3af;
    }
    .empty-state i {
        font-size: 2.5rem;
        margin-bottom: 8px;
        display: block;
    }

    /* Categories */
    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 8px;
        max-height: 350px;
        overflow-y: auto;
        padding: 4px;
    }
    .category-check-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border: 1px solid #e8e8e8;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .category-check-item:hover {
        border-color: #c7d2fe;
        background: #f8f9ff;
    }
    .category-check-item:has(input:checked) {
        border-color: #2557a7;
        background: #eef2ff;
    }
    .category-check-label {
        font-size: 0.85rem;
        font-weight: 500;
        color: #374151;
    }

    /* Skills tags */
    .skill-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: linear-gradient(135deg, #eef2ff, #e8f0fe);
        color: #2557a7;
        padding: 6px 12px;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        border: 1px solid rgba(37, 87, 167, 0.1);
    }
    .skill-tag-remove {
        background: none;
        border: none;
        color: #2557a7;
        opacity: 0.5;
        cursor: pointer;
        padding: 0;
        line-height: 1;
        font-size: 1rem;
        transition: opacity 0.2s;
    }
    .skill-tag-remove:hover {
        opacity: 1;
        color: #dc2626;
    }

    /* Entry list (education, experience) */
    .entry-list-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    .entry-list-item:last-child {
        border-bottom: none;
    }
    .entry-list-marker {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #2557a7;
        margin-top: 6px;
        flex-shrink: 0;
    }
    .entry-delete {
        width: 30px;
        height: 30px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0.4;
        transition: opacity 0.2s;
        flex-shrink: 0;
    }
    .entry-list-item:hover .entry-delete {
        opacity: 1;
    }
    .add-entry-form {
        background: #f9fafb;
        border: 1px dashed #d1d5db;
        border-radius: 10px;
        padding: 16px;
    }

    /* Language level badges */
    .language-level-badge {
        font-size: 0.7rem;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 50px;
    }
    .language-level-badge.level-nativo { background: #dcfce7; color: #166534; }
    .language-level-badge.level-fluente { background: #dbeafe; color: #1e40af; }
    .language-level-badge.level-avancado { background: #e0e7ff; color: #3730a3; }
    .language-level-badge.level-intermediario { background: #fef3c7; color: #92400e; }
    .language-level-badge.level-basico { background: #f3f4f6; color: #6b7280; }

    /* Share */
    .share-link-box {
        display: flex;
        align-items: center;
        background: #f3f4f6;
        border-radius: 8px;
        padding: 4px;
        gap: 4px;
    }
    .share-link-url {
        flex-grow: 1;
        padding: 6px 10px;
        font-size: 0.78rem;
        color: #374151;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .share-link-copy {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 6px 10px;
        cursor: pointer;
        color: #6b7280;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    .share-link-copy:hover {
        background: #2557a7;
        color: #fff;
        border-color: #2557a7;
    }
    .share-social-btn {
        font-size: 0.78rem;
        font-weight: 600;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 6px 8px;
    }
    .share-fb { background: #3b5998; }
    .share-fb:hover { background: #2d4373; color: #fff; }
    .share-li { background: #0077b5; }
    .share-li:hover { background: #005e8e; color: #fff; }
    .share-wa { background: #25D366; }
    .share-wa:hover { background: #1da851; color: #fff; }

    /* Subscription */
    .subscription-status {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        border-radius: 10px;
        background: #f9fafb;
    }
    .subscription-status-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* Course mini items */
    .course-mini-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    .course-mini-item:last-child { border-bottom: none; }
    .course-mini-icon {
        width: 30px;
        height: 30px;
        border-radius: 6px;
        background: #eef2ff;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #4f46e5;
        font-size: 0.85rem;
        flex-shrink: 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .profile-mgmt-hero { padding: 24px 0 20px; }
        .profile-mgmt-avatar { width: 64px; height: 64px; border-radius: 12px; }
        .profile-mgmt-name { font-size: 1.2rem; }
        .profile-mgmt-nav .nav-link span { display: none; }
        .mgmt-card-body { padding: 14px 16px 18px; }
        .avatar-upload-zone { flex-direction: column; }
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Restore active tab from session storage
        const activeTab = sessionStorage.getItem('profileActiveTab');
        if (activeTab) {
            const tabButton = document.querySelector(`button[data-bs-target="${activeTab}"]`);
            if (tabButton) {
                document.querySelectorAll('#profileTabs .nav-link').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('#profileTabsContent .tab-pane').forEach(pane => pane.classList.remove('show', 'active'));
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

        // Bio character counter
        const bioTextarea = document.getElementById('bio-textarea');
        const bioCount = document.getElementById('bio-count');
        if (bioTextarea && bioCount) {
            bioTextarea.addEventListener('input', function() {
                bioCount.textContent = this.value.length;
            });
        }
    });

    function copyPublicLink() {
        var linkValue = document.getElementById("publicProfileLink").value;
        
        navigator.clipboard.writeText(linkValue).then(function() {
            var btn = document.getElementById('copyLinkBtn');
            if (btn) {
                var originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check2"></i>';
                setTimeout(function() {
                    btn.innerHTML = originalHtml;
                }, 2000);
            }
        });
    }
</script>
@endsection

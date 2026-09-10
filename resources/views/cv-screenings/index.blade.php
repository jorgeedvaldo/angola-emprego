@extends('templates.app')
@section('title', 'Analisador de CVs')
@section('description', 'Escreva a descrição da vaga, carregue os CVs e receba os candidatos ordenados por compatibilidade.')

@section('content')
<div class="bg-light py-4">
    <div class="container">
        <a href="{{ route('recruiters.index') }}" class="small text-decoration-none">&larr; Empresas e Recrutadores</a>
        <h1 class="fw-bold mt-2 mb-1">Analisador de CVs</h1>
        <p class="text-muted mb-0">
            Escreva a descrição da vaga, carregue os CVs que tem em mão e a análise ordena-os do mais
            compatível para o menos compatível.
        </p>
    </div>
</div>

<section class="py-5">
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Nova triagem</h5>
                        <form method="POST" action="{{ route('cv-screenings.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="title" class="form-label fw-semibold">Nome da vaga</label>
                                <input type="text" id="title" name="title" maxlength="255" required
                                    class="form-control @error('title') is-invalid @enderror"
                                    value="{{ old('title') }}" placeholder="Ex.: Técnico de Manutenção Industrial">
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label fw-semibold">Descrição da vaga</label>
                                <textarea id="description" name="description" rows="10" required maxlength="20000"
                                    class="form-control @error('description') is-invalid @enderror"
                                    placeholder="Descreva as funções, os requisitos, a formação e a experiência pretendida. Quanto mais detalhada a descrição, melhor a ordenação.">{{ old('description') }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="cvs" class="form-label fw-semibold">CVs (PDF)</label>
                                <input type="file" id="cvs" name="cvs[]" multiple accept="application/pdf,.pdf" required
                                    class="form-control @error('cvs') is-invalid @enderror @error('cvs.*') is-invalid @enderror">
                                <div class="form-text">
                                    Até {{ \App\Models\CvScreening::MAX_CVS_PER_UPLOAD }} ficheiros de cada vez,
                                    no máximo 5 MB cada. Só PDF — é o formato de que conseguimos ler o texto.
                                </div>
                                @error('cvs')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                @error('cvs.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <button type="submit" class="btn btn-primary fw-bold" style="background-color: #2557a7; border-color: #2557a7;">
                                <i class="bi bi-stars me-1"></i> Criar triagem
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">Como funciona</h6>
                        <ol class="text-muted small mb-0 ps-3">
                            <li class="mb-2">Escreve a descrição da vaga, como a publicaria num anúncio.</li>
                            <li class="mb-2">Carrega os CVs em PDF, quantos quiser (até {{ \App\Models\CvScreening::MAX_CVS_PER_SCREENING }} por triagem).</li>
                            <li class="mb-2">Clica em “Analisar CVs”: lemos o texto de cada CV e comparamos com a descrição.</li>
                            <li>Recebe a lista ordenada, do candidato mais compatível para o menos compatível.</li>
                        </ol>
                    </div>
                </div>

                <h6 class="fw-bold mb-3">As suas triagens</h6>
                @forelse($screenings as $screening)
                    <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
                        <div class="card-body p-3 d-flex justify-content-between align-items-center gap-3">
                            <div class="min-width-0">
                                <a href="{{ route('cv-screenings.show', $screening) }}" class="fw-bold text-decoration-none text-dark">
                                    {{ $screening->title }}
                                </a>
                                <div class="small text-muted">
                                    {{ $screening->candidates_count }} CV(s) · {{ $screening->created_at->format('d/m/Y') }}
                                </div>
                            </div>
                            <a href="{{ route('cv-screenings.show', $screening) }}" class="btn btn-sm btn-outline-primary text-nowrap">Abrir</a>
                        </div>
                    </div>
                @empty
                    <div class="bg-white p-4 text-center rounded-3 shadow-sm border text-muted small">
                        Ainda não criou nenhuma triagem.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection

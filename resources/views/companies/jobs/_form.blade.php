@php
    $selectedCategories = $selectedCategories ?? [];
@endphp

<div class="mb-3">
    <label class="form-label fw-semibold">Título da vaga</label>
    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $job->title ?? '') }}" required>
    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label fw-semibold">Localização</label>
    <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location', $job->location ?? $company->location) }}" required>
    @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label fw-semibold">Descrição</label>
    <input type="hidden" id="description-input" name="description" value="{{ old('description', $job->description ?? '') }}">
    <trix-editor input="description-input" class="trix-content form-control @error('description') is-invalid @enderror" style="min-height: 220px;"></trix-editor>
    @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>

@once
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trix@2.1.0/dist/trix.css">
    <script src="https://cdn.jsdelivr.net/npm/trix@2.1.0/dist/trix.umd.min.js"></script>
    <style>
        trix-toolbar .trix-button-group--file-tools { display: none; }
    </style>
    <script>
        document.addEventListener('trix-file-accept', function (event) {
            event.preventDefault();
        });
    </script>
@endonce

<div class="mb-3">
    <label class="form-label fw-semibold">Email ou link de candidatura (opcional)</label>
    <input type="text" name="email_or_link" class="form-control" value="{{ old('email_or_link', $job->email_or_link ?? $company->email) }}" placeholder="Os candidatos também podem enviar o CV nesta página">
    <small class="text-muted">Se deixar em branco, usamos o email da empresa. As candidaturas com CV continuam disponíveis na página da vaga.</small>
</div>

@if($categories->isNotEmpty())
<div class="mb-4">
    <label class="form-label fw-semibold">Categorias</label>
    <div class="d-flex flex-wrap gap-2">
        @foreach($categories as $category)
            <label class="badge bg-light text-dark border p-2">
                <input type="checkbox" name="categories[]" value="{{ $category->id }}" class="form-check-input me-1"
                    {{ in_array($category->id, old('categories', $selectedCategories)) ? 'checked' : '' }}>
                {{ $category->name }}
            </label>
        @endforeach
    </div>
</div>
@endif

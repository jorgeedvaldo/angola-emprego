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
    <textarea name="description" rows="10" class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $job->description ?? '') }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

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

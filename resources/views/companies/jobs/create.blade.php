@extends('templates.app')
@section('title', 'Publicar vaga')
@section('description', 'Publique uma nova vaga na página da sua empresa')

@section('content')
<div class="bg-light py-4">
    <div class="container">
        <h1 class="fw-bold">Publicar vaga</h1>
        <p class="text-muted mb-0">A vaga aparece na página <strong>/company/{{ $company->slug }}</strong> e na listagem de vagas.</p>
    </div>
</div>

<section class="py-5">
    <div class="container" style="max-width: 760px;">
        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-body p-4 p-lg-5">
                <form method="POST" action="{{ route('company.jobs.store') }}">
                    @csrf
                    @include('companies.jobs._form')
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary fw-bold" style="background-color: #2557a7; border-color: #2557a7;">Publicar vaga</button>
                        <a href="{{ route('company.dashboard') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

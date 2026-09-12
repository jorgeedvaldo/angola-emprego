@extends('templates.app')
@section('title', __('site.vaga_form.publicar_titulo'))
@section('description', __('site.vaga_form.publicar_descricao'))

@section('content')
<div class="bg-light py-4">
    <div class="container">
        <h1 class="fw-bold">{{ __('site.vaga_form.publicar_titulo') }}</h1>
        <p class="text-muted mb-0">{!! __('site.vaga_form.publicar_nota', ['url' => e('/company/' . $company->slug)]) !!}</p>
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
                        <button type="submit" class="btn btn-primary fw-bold" style="background-color: #2557a7; border-color: #2557a7;">{{ __('site.vaga_form.publicar_botao') }}</button>
                        <a href="{{ route('company.dashboard') }}" class="btn btn-outline-secondary">{{ __('site.vaga_form.cancelar') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

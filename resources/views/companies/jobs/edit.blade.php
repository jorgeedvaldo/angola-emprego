@extends('templates.app')
@section('title', __('site.vaga_form.editar_titulo'))
@section('description', __('site.vaga_form.editar_descricao'))

@section('content')
<div class="bg-light py-4">
    <div class="container">
        <h1 class="fw-bold">{{ __('site.vaga_form.editar_titulo') }}</h1>
    </div>
</div>

<section class="py-5">
    <div class="container" style="max-width: 760px;">
        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-body p-4 p-lg-5">
                <form method="POST" action="{{ route('company.jobs.update', $job) }}">
                    @csrf
                    @method('PUT')
                    @include('companies.jobs._form')
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary fw-bold" style="background-color: #2557a7; border-color: #2557a7;">{{ __('site.vaga_form.guardar_alteracoes') }}</button>
                        <a href="{{ route('company.dashboard') }}" class="btn btn-outline-secondary">{{ __('site.vaga_form.cancelar') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

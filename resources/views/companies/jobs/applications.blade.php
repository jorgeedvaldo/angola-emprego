@extends('templates.app')
@section('title', 'Candidaturas — ' . $job->title)
@section('description', 'Candidaturas recebidas para a vaga')

@section('content')
<div class="bg-light py-4">
    <div class="container">
        <a href="{{ route('company.dashboard') }}" class="small text-decoration-none">&larr; Voltar ao painel</a>
        <h1 class="fw-bold mt-2">Candidaturas</h1>
        <p class="text-muted mb-0">{{ $job->title }}</p>
    </div>
</div>

<section class="py-5">
    <div class="container">
        @forelse($applications as $application)
            <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                        <div>
                            <h5 class="fw-bold mb-0">{{ $application->name }}</h5>
                            <div class="small text-muted">{{ $application->email }} @if($application->phone)· {{ $application->phone }}@endif</div>
                        </div>
                        <div class="text-muted small">{{ $application->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <p class="fw-semibold mb-1">Assunto: {{ $application->subject }}</p>
                    <p class="mb-3">{{ $application->message }}</p>
                    <a href="{{ route('company.applications.download', $application) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-download me-1"></i> Descarregar CV ({{ $application->attachment_name }})
                    </a>
                </div>
            </div>
        @empty
            <div class="bg-white p-5 text-center rounded-3 shadow-sm border text-muted">
                Ainda não há candidaturas para esta vaga.
            </div>
        @endforelse

        <div class="mt-3">{{ $applications->links() }}</div>
    </div>
</section>
@endsection

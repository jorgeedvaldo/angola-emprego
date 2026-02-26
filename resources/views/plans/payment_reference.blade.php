@extends('templates.app')

@section('title', 'Pagamento por Referência')

@section('content')
<section class="py-5 bg-light h-100">
    <div class="container h-100">
        <div class="card shadow-lg border-0 h-100" style="min-height: 70vh;">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="mb-0 fw-bold text-primary"><i class="bi bi-upc-scan me-2"></i>Pagamento por Referência</h4>
                        <small class="text-muted">Utilize o Multicaixa Express ou um ATM para pagar</small>
                    </div>
                     <div class="d-flex align-items-center text-warning bg-light px-3 py-2 rounded-pill border" id="status-badge">
                        <div class="spinner-border spinner-border-sm me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="fw-bold">A aguardar confirmação...</span>
                    </div>
                </div>
            </div>
            
            <div class="card-body d-flex flex-column align-items-center justify-content-center bg-white p-5">
                
                <div class="text-center mb-4">
                    <h5 class="text-muted mb-2">Entidade Kuenha</h5>
                    <h2 class="fw-bold text-dark tracking-widest">10061</h2>
                </div>
                
                <div class="text-center mb-4">
                    <h5 class="text-muted mb-2">Referência</h5>
                    <h1 class="fw-bold text-primary" style="letter-spacing: 2px;">{{ number_format($reference, 0, '', ' ') }}</h1>
                </div>
                
                <div class="text-center mb-4">
                    <h5 class="text-muted mb-2">Total a Pagar</h5>
                    <h2 class="fw-bold text-success">{{ number_format($total, 2, ',', '.') }} Kz</h2>
                </div>
                
                <div class="alert alert-info border-0 shadow-sm mt-4 text-center">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    Aceda ao menu <strong>Pagamentos > Pagamento por Referência</strong> no Multicaixa Express ou ATM.
                </div>

            </div>
             <div class="card-footer bg-light text-center py-3">
                <small class="text-muted"><i class="bi bi-info-circle-fill text-primary me-1"></i> Esta página será atualizada automaticamente assim que o pagamento for detetado.</small>
            </div>
        </div>
    </div>
</section>
@endsection

@section('footer-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const subscriptionRequestId = "{{ $subscriptionRequestId }}";
        const checkStatusUrl = "{{ route('subscription.check_status', ':id') }}".replace(':id', subscriptionRequestId);
        const successUrl = "{{ route('profile.show') }}";
        const statusBadge = document.getElementById('status-badge');

        let pollInterval = setInterval(() => {
            fetch(checkStatusUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'approved') {
                        clearInterval(pollInterval);
                        statusBadge.classList.remove('text-warning', 'bg-light', 'border');
                        statusBadge.classList.add('text-white', 'bg-success');
                        statusBadge.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i><span class="fw-bold">Pagamento Confirmado!</span>';
                        
                        setTimeout(() => {
                             window.location.href = successUrl;
                        }, 1500);
                    }
                })
                .catch(error => console.error('Error checking status:', error));
        }, 5000);
    });
</script>
@endsection

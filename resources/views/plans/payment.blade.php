@extends('templates.app')

@section('title', 'Pagamento Seguro')

@section('content')
<section class="py-5 bg-light h-100">
    <div class="container h-100">
        <div class="card shadow-lg border-0 h-100" style="min-height: 80vh;">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="mb-0 fw-bold text-primary"><i class="bi bi-credit-card-2-front me-2"></i>Pagamento Seguro</h4>
                        <small class="text-muted">Finalize o pagamento via Multicaixa Express</small>
                    </div>
                     <div class="d-flex align-items-center text-warning bg-light px-3 py-2 rounded-pill border" id="status-badge">
                        <div class="spinner-border spinner-border-sm me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="fw-bold">A aguardar confirmação...</span>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-0 d-flex flex-column position-relative bg-white">
                <!-- iFrame Container -->
                <div class="flex-grow-1 w-100" style="height: 700px; min-height: 60vh;">
                     <iframe 
                        src="https://pagamentonline.emis.co.ao/online-payment-gateway/webframe/frame?token={{ $token }}"
                        class="w-100 h-100 border-0"
                        allowfullscreen
                    ></iframe>
                </div>
            </div>
             <div class="card-footer bg-light text-center py-3">
                <small class="text-muted"><i class="bi bi-info-circle-fill text-primary me-1"></i> Por favor, <strong>não feche esta janela</strong> até o pagamento ser confirmado automaticamente.</small>
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
                        
                        // Optional: Add a toast or larger notification
                        setTimeout(() => {
                             window.location.href = successUrl;
                        }, 1500);
                    }
                })
                .catch(error => console.error('Error checking status:', error));
        }, 3000); // Check every 3 seconds for faster feedback
    });
</script>
@endsection

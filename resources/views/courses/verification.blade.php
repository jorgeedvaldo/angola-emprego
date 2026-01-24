@extends('templates.app')

@section('title', 'Verificação de Certificado')
@section('description', 'Verifique a autenticidade do certificado de conclusão de curso.')

@section('content')
<div class="bg-light py-5">
    <div class="container">
         <h1 class="fw-bold mb-2 text-dark">Verificação de Certificado</h1>
         <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{url('/')}}">Início</a></li>
                <li class="breadcrumb-item active" aria-current="page">Validar Certificado</li>
            </ol>
        </nav>
    </div>
</div>

<section class="section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-lg rounded-3 overflow-hidden">
                    <div class="card-header bg-success text-white py-3 text-center">
                        <h4 class="mb-0 fw-bold"><i class="bi bi-patch-check-fill me-2"></i> Certificado Válido</h4>
                    </div>
                    <div class="card-body p-4 text-center">
                        
                        <div class="mb-4">
                            <h5 class="text-muted">Este certificado confirma que</h5>
                            <h2 class="fw-bold text-dark">{{ $user->name }}</h2>
                            <h5 class="text-muted">concluiu com sucesso o curso</h5>
                            <h3 class="text-primary fw-bold">{{ $course->title }}</h3>
                            <p class="text-muted mt-2">Data de Conclusão: <strong>{{ $completionDate->format('d/m/Y') }}</strong></p>
                        </div>

                        <!-- Certificate Visual Representation -->
                        <!-- Using aspect-ratio to keep it responsive and maintain design -->
                        <div class="certificate-container shadow-sm mb-4 mx-auto position-relative" style="max-width: 900px;">
                            <style>
                                .certificate-container {
                                    width: 100%;
                                    aspect-ratio: 1.414 / 1; /* A4 Ratio approx */
                                    background-image: url('{{ asset('assets/img/certificate_bg.png') }}');
                                    background-size: 100% 100%;
                                    background-repeat: no-repeat;
                                    position: relative;
                                }
                                .cert-content {
                                    position: absolute;
                                    top: 0;
                                    left: 0;
                                    width: 100%;
                                    height: 100%;
                                    display: flex;
                                    flex-direction: column;
                                    justify-content: center;
                                    align-items: center;
                                    text-align: center;
                                    font-family: 'Times New Roman', serif;
                                    color: #333;
                                    padding: 5%;
                                }
                                .cert-text {
                                    font-size: 2.2cqw; /* Container Query Width relative unit fallback or viewport */
                                    font-size: 2.2cqi; 
                                    line-height: 1.6;
                                }
                                
                                /* Fallback for browsers not supporting container queries units in this context well yet, use percentages */
                                .cert-text {
                                    font-size: 3.5%; 
                                }

                                .cert-name {
                                    font-weight: bold;
                                    font-size: 200%; /* Relative to container font size */
                                    border-bottom: 2px solid #333;
                                    display: inline-block;
                                    min-width: 40%;
                                    margin: 10px 0;
                                }
                                .cert-course {
                                    font-weight: bold;
                                    font-size: 180%;
                                    color: #000;
                                }
                                .cert-date {
                                    font-weight: bold;
                                }
                                .cert-footer {
                                    position: absolute;
                                    bottom: 10%;
                                    width: 100%;
                                    text-align: center;
                                    font-size: 120%;
                                    color: #666;
                                }
                                /* Responsive text sizing based on container width hack */
                                .certificate-container {
                                    font-size: 1.5vw; 
                                }
                                @media (min-width: 992px) {
                                    .certificate-container {
                                        font-size: 14px; /* Base size for large screens if we cap max-width */
                                    }
                                }
                            </style>
                            
                            <div class="cert-content">
                                <!-- Spacing from top -->
                                <div style="height: 25%;"></div> 
                                
                                <div style="font-size: 1.5em; line-height: 1.5;">
                                    Este certificado é concedido a <br>
                                    <span class="cert-name">{{ $user->name }}</span><br>
                                    pela conclusão bem-sucedida do curso/programa<br>
                                    <span class="cert-course">{{ $course->title }}</span><br>
                                    em <span class="cert-date">{{ $completionDate->format('d/m/Y') }}</span>.
                                </div>

                                <div class="cert-footer">
                                    angolaemprego.com
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-md-row justify-content-center gap-3">
                            <a href="{{ route('courses.show', $course->slug) }}" class="btn btn-outline-secondary rounded-pill">
                                <i class="bi bi-info-circle me-2"></i> Ver Detalhes do Curso
                            </a>
                            @auth
                                @if(Auth::id() === $user->id)
                                <a href="{{ route('courses.certificate', $course->slug) }}" class="btn btn-primary rounded-pill fw-bold">
                                    <i class="bi bi-download me-2"></i> Baixar PDF
                                </a>

                                @php
                                    $linkedinUrl = "https://www.linkedin.com/profile/add?startTask=CERTIFICATION_NAME&";
                                    $linkedinParams = http_build_query([
                                        'name' => $course->title,
                                        'organizationId' => 99975145,
                                        'issueYear' => $completionDate->format('Y'),
                                        'issueMonth' => $completionDate->format('m'),
                                        'certUrl' => route('certificates.verify', ['user' => $user->id, 'course' => $course->slug]),
                                        'certId' => $user->id . '-' . $course->id
                                    ]);
                                    $linkedinButtonUrl = $linkedinUrl . $linkedinParams;
                                @endphp

                                <a href="{{ $linkedinButtonUrl }}" target="_blank" class="btn btn-primary rounded-pill fw-bold" style="background-color: #0077b5; border-color: #0077b5;">
                                    <i class="bi bi-linkedin me-2"></i> Adicionar ao LinkedIn
                                </a>
                                @endif
                            @endauth
                        </div>
                    
                        <div class="mt-4 pt-3 border-top">
                            <p class="text-muted small mb-2">Link público para verificação:</p>
                            <div class="input-group mb-3 mx-auto" style="max-width: 600px;">
                                <input type="text" class="form-control" value="{{ route('certificates.verify', ['user' => $user->id, 'course' => $course->slug]) }}" id="verifyLink" readonly>
                                <button class="btn btn-outline-primary" type="button" onclick="copyLink()">
                                    <i class="bi bi-clipboard"></i> Copiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@section('footer-scripts')
<script>
    function copyLink() {
        var copyText = document.getElementById("verifyLink");
        copyText.select();
        copyText.setSelectionRange(0, 99999); 
        navigator.clipboard.writeText(copyText.value);
        alert("Link copiado: " + copyText.value);
    }
</script>
@endsection

@endsection

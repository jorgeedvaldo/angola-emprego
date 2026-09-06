@extends('templates.app')
@section('title', 'Como funciona a análise de CV por inteligência artificial')
@section('description', 'Entenda de forma simples como a sua empresa pode publicar vagas e usar a análise automática de currículos do Angola Emprego, gratuitamente.')
@section('canonical_link', url('/analise-de-cv'))

@section('content')
    <!-- Page Header -->
    <div class="bg-light py-5">
        <div class="container text-center">
            <h1 class="fw-bold mb-3 text-dark">Como funciona a análise de CV por inteligência artificial</h1>
            <p class="text-muted mx-auto" style="max-width: 640px;">
                Uma explicação simples de como a sua empresa pode publicar vagas e deixar o computador ajudar a
                encontrar os melhores candidatos, sem custos.
            </p>
        </div>
    </div>

    <!-- 4 passos -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <span class="text-primary fw-bold text-uppercase small">Passo a passo</span>
                <h2 class="fw-bold text-dark mt-2">Do cadastro até encontrar o candidato certo</h2>
            </div>

            <div class="row gy-4">
                <div class="col-md-6 col-lg-3">
                    <div class="p-4 border rounded-3 h-100 bg-light">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width:36px;height:36px;">1</div>
                            <i class="bi bi-building fs-3 text-primary"></i>
                        </div>
                        <h5 class="fw-bold">Cria a conta da sua empresa</h5>
                        <p class="text-muted small mb-0">
                            Regista a tua empresa com um email. Não pagas nada por isso. É só preencher um formulário
                            curto, como quando criamos uma conta em qualquer aplicação.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="p-4 border rounded-3 h-100 bg-light">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width:36px;height:36px;">2</div>
                            <i class="bi bi-person-badge fs-3 text-primary"></i>
                        </div>
                        <h5 class="fw-bold">Monta a página da tua empresa</h5>
                        <p class="text-muted small mb-0">
                            Depois de criar a conta, fazes uma página só da tua empresa: nome, logotipo, uma pequena
                            descrição e os contactos. É como um perfil, para as pessoas saberem quem está a contratar.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="p-4 border rounded-3 h-100 bg-light">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width:36px;height:36px;">3</div>
                            <i class="bi bi-briefcase fs-3 text-primary"></i>
                        </div>
                        <h5 class="fw-bold">Publica a vaga</h5>
                        <p class="text-muted small mb-0">
                            Escreves o que precisas: o cargo, o que a pessoa vai fazer e o que ela precisa de saber ou
                            ter. A vaga fica visível para milhares de pessoas que procuram emprego em Angola.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="p-4 border rounded-3 h-100 bg-light">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width:36px;height:36px;">4</div>
                            <i class="bi bi-robot fs-3 text-primary"></i>
                        </div>
                        <h5 class="fw-bold">O sistema lê os currículos por ti</h5>
                        <p class="text-muted small mb-0">
                            Quando chegam as candidaturas, clicas num botão e o sistema lê a vaga e cada currículo
                            recebido, e mostra os candidatos ordenados dos que mais combinam até aos que menos combinam.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Explicação simples do passo 4 -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center gy-5">
                <div class="col-lg-6">
                    <span class="text-primary fw-bold text-uppercase small">A parte da inteligência artificial</span>
                    <h2 class="fw-bold text-dark mt-2 mb-4">Porque é que isto é útil?</h2>
                    <p class="text-muted mb-3">
                        Imagina que publicas uma vaga e recebes 5 candidaturas. Não é difícil ler todos os currículos
                        um por um, com calma.
                    </p>
                    <p class="text-muted mb-3">
                        Agora imagina que recebes 200 candidaturas para essa mesma vaga. Já não dá para ler tudo com
                        atenção — vais perder horas, e ainda assim podes deixar passar o candidato certo, só porque o
                        currículo dele estava lá no meio dos outros 199.
                    </p>
                    <p class="text-muted mb-0">
                        É para isto que serve a análise automática: o sistema lê tudo por ti e organiza os candidatos
                        numa lista, do que mais parece combinar com a vaga até ao que menos combina. Tu continuas a
                        decidir quem contratar — o sistema só te ajuda a não perder tempo a abrir currículo por
                        currículo.
                    </p>
                </div>
                <div class="col-lg-6">
                    <div class="p-4 p-lg-5 bg-white rounded-3 shadow-sm border">
                        <h5 class="fw-bold mb-3">Como o sistema sabe quem combina mais?</h5>
                        <p class="text-muted mb-3">
                            Pensa assim: o sistema lê o texto da vaga e o texto de cada currículo, e transforma cada um
                            numa espécie de impressão digital do que está escrito ali.
                        </p>
                        <p class="text-muted mb-3">
                            Depois compara essas impressões digitais. Se o currículo fala das mesmas coisas que a vaga
                            pede — a mesma experiência, os mesmos certificados, as mesmas palavras técnicas — as
                            impressões digitais ficam parecidas, e o candidato recebe uma percentagem alta de
                            compatibilidade.
                        </p>
                        <p class="text-muted mb-0">
                            Não é magia, é comparação de texto. E como qualquer ferramenta, não acerta sempre a
                            cem por cento — por isso a percentagem é uma ajuda para saberes por onde começar a olhar,
                            não uma decisão automática. A palavra final é sempre tua.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold text-dark">Perguntas Frequentes</h2>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion accordion-flush" id="cvAnalysisFaqAccordion">
                        <div class="accordion-item border mb-3 rounded shadow-sm overflow-hidden">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#cvfaq1">
                                    Preciso de pagar alguma coisa?
                                </button>
                            </h2>
                            <div id="cvfaq1" class="accordion-collapse collapse" data-bs-parent="#cvAnalysisFaqAccordion">
                                <div class="accordion-body text-muted">Não. Criar a conta da empresa, publicar vagas e usar a análise de currículos é totalmente gratuito.</div>
                            </div>
                        </div>

                        <div class="accordion-item border mb-3 rounded shadow-sm overflow-hidden">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#cvfaq2">
                                    O sistema escolhe quem eu devo contratar?
                                </button>
                            </h2>
                            <div id="cvfaq2" class="accordion-collapse collapse" data-bs-parent="#cvAnalysisFaqAccordion">
                                <div class="accordion-body text-muted">Não. Ele só organiza os candidatos por compatibilidade com a vaga, para poupar o teu tempo. Quem decide contratar és sempre tu.</div>
                            </div>
                        </div>

                        <div class="accordion-item border mb-3 rounded shadow-sm overflow-hidden">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#cvfaq3">
                                    Que tipo de currículo o sistema consegue ler?
                                </button>
                            </h2>
                            <div id="cvfaq3" class="accordion-collapse collapse" data-bs-parent="#cvAnalysisFaqAccordion">
                                <div class="accordion-body text-muted">Currículos em ficheiro PDF, sejam feitos no computador ou digitalizados a partir de papel.</div>
                            </div>
                        </div>

                        <div class="accordion-item border mb-3 rounded shadow-sm overflow-hidden">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#cvfaq4">
                                    Preciso de saber alguma coisa de tecnologia para usar isto?
                                </button>
                            </h2>
                            <div id="cvfaq4" class="accordion-collapse collapse" data-bs-parent="#cvAnalysisFaqAccordion">
                                <div class="accordion-body text-muted">Não. Depois de a vaga estar publicada e as candidaturas chegarem, é só clicar num botão chamado "Analisar candidaturas" na tua página de empresa.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA final -->
    <section class="py-5 bg-primary text-white text-center">
        <div class="container">
            <h2 class="fw-bold mb-3">Pronto para começar?</h2>
            <p class="mb-4 opacity-75">Cria a conta da tua empresa e publica a tua primeira vaga hoje mesmo.</p>
            <a href="{{ route('register.company') }}" class="btn btn-light btn-lg rounded-pill fw-bold px-4">
                <i class="bi bi-building-add me-2"></i> Cadastrar a minha empresa
            </a>
        </div>
    </section>
@endsection

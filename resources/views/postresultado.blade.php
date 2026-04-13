@extends('templates.app')
@section('title', 'Resultados da Prova do Concurso do CSMJ (Conselho Superior da Magistratura Judicial)')
@section('description', 'Consulte a lista provisória de classificação final do concurso público do CSMJ - Conselho Superior da Magistratura Judicial de Angola 2026. Pesquise pelo seu nome e veja o seu resultado.')
@section('canonical_link', url('/noticias/resultados-concurso-csmj-2026'))
@section('og_type', 'article')
@section('created_at', '2026-04-13T00:00:00+01:00')
@section('updated_at', '2026-04-13T00:00:00+01:00')

@section('head-scripts')
<script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "NewsArticle",
      "headline": "Veja Os Resultados da Prova do Concurso do CSMJ (Conselho Superior da Magistratura Judicial)",
      "description": "Consulte a lista provisória de classificação final do concurso público do CSMJ 2026. Pesquise pelo seu nome e veja o resultado da sua candidatura.",
      "url": "{{ url('/noticias/resultados-concurso-csmj-2026') }}",
      "image": ["https://angolaemprego.com/assets/img/logo.svg"],
      "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "{{ url('/noticias/resultados-concurso-csmj-2026') }}"
      },
      "datePublished": "2026-04-13T00:00:00+01:00",
      "dateModified": "2026-04-13T00:00:00+01:00",
      "author": [{"@type": "Person", "name": "Angola Emprego", "url": "{{ url('/') }}"}],
      "publisher": {
        "@type": "Organization",
        "name": "Angola Emprego",
        "logo": {"@type": "ImageObject", "url": "https://angolaemprego.com/assets/img/logo.svg"}
      }
    }
</script>
<script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "BlogPosting",
      "headline": "Veja Os Resultados da Prova do Concurso do CSMJ (Conselho Superior da Magistratura Judicial)",
      "description": "Lista provisória de classificação final do concurso público do CSMJ Angola 2026.",
      "image": {"@type": "ImageObject", "url": "https://angolaemprego.com/assets/img/logo.svg", "width": 1200, "height": 675},
      "author": {"@type": "Person", "name": "Angola Emprego", "url": "{{ url('/') }}"},
      "publisher": {
        "@type": "Organization",
        "name": "Angola Emprego",
        "logo": {"@type": "ImageObject", "url": "https://angolaemprego.com/assets/img/logo.svg"}
      },
      "datePublished": "2026-04-13T00:00:00+01:00",
      "dateModified": "2026-04-13T00:00:00+01:00",
      "mainEntityOfPage": {"@type": "WebPage", "@id": "{{ url('/noticias/resultados-concurso-csmj-2026') }}"}
    }
</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [{
    "@type": "ListItem",
    "position": 1,
    "name": "Início",
    "item": "{{ url('/') }}"
  },{
    "@type": "ListItem",
    "position": 2,
    "name": "Notícias",
    "item": "{{ url('/noticias') }}"
  },{
    "@type": "ListItem",
    "position": 3,
    "name": "Resultados Concurso CSMJ 2026",
    "item": "{{ url('/noticias/resultados-concurso-csmj-2026') }}"
  }]
}
</script>
<meta property="article:section" content="Concursos Públicos" />
@endsection

@section('content')
    <!-- Page Header -->
    <div class="bg-light py-5">
      <div class="container">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-4">
             <li class="breadcrumb-item"><a href="{{url('/')}}">Início</a></li>
             <li class="breadcrumb-item"><a href="{{url('/noticias')}}">Notícias</a></li>
             <li class="breadcrumb-item active" aria-current="page">Resultados Concurso CSMJ</li>
          </ol>
        </nav>
        
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center">
                <h1 class="display-5 fw-bold text-dark mb-4">Veja Os Resultados da Prova do Concurso do CSMJ (Conselho Superior da Magistratura Judicial)</h1>
                <div class="d-flex justify-content-center align-items-center gap-3 text-muted">
                     <span><i class="bi bi-person-fill me-1"></i> Angola Emprego</span>
                     <span><i class="bi bi-calendar-event me-1"></i> 13/04/2026</span>
                </div>
            </div>
        </div>
      </div>
    </div>

    <!-- Article Section -->
    <section class="section py-5">
      <div class="container">
        <div class="row g-5 justify-content-center">
          <div class="col-lg-8">

            <div class="bg-white p-lg-5 p-4 rounded-3 text-dark shadow-sm border mb-4 article-content">
                <!-- Botões de compartilhamento -->
                <div class="d-flex gap-2 mb-4">
                    <a class="btn btn-outline-primary btn-sm rounded-pill px-3" href="https://www.facebook.com/sharer/sharer.php?u={{ url('/noticias/resultados-concurso-csmj-2026') }}" target="_blank"><i class="bi bi-facebook me-1"></i> Partilhar</a>
                    <a class="btn btn-outline-success btn-sm rounded-pill px-3" href="https://api.whatsapp.com/send?text=Resultados do Concurso CSMJ 2026 - Pesquise pelo seu nome: {{ url('/noticias/resultados-concurso-csmj-2026') }}" target="_blank"><i class="bi bi-whatsapp me-1"></i> WhatsApp</a>
                </div>

                <div class="mb-4">
                    <p class="lead">O <strong>Conselho Superior da Magistratura Judicial (CSMJ)</strong> publicou a <strong>Lista Provisória de Classificação Final</strong> do concurso público para ingresso na carreira de funcionário judicial.</p>
                    
                    <p>Este concurso, que atraiu milhares de candidatos em toda Angola, avaliou os conhecimentos dos participantes através de provas escritas realizadas a nível nacional. A lista abrange os cargos de <strong>Escrivão de Direito de 3ª Classe</strong> e <strong>Oficial de Diligências de 3ª Classe</strong>.</p>

                    <div class="alert alert-info border-0 shadow-sm" role="alert">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-info-circle-fill fs-4 me-3 text-info"></i>
                            <div>
                                <strong>Como consultar o seu resultado:</strong><br>
                                Utilize a barra de pesquisa abaixo para procurar pelo seu <strong>nome completo</strong> ou parte do nome. O sistema mostrará imediatamente o seu resultado, incluindo a nota obtida e o estado da candidatura.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PDF Downloads -->
                <div class="csmj-downloads mb-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>Descarregar Listas em PDF</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="https://csmj.ao/storage/Document/FDm6TvE6Nz41Vragg9f90OK1ajOAPHALbwUHPq6G.pdf" target="_blank" class="csmj-download-card" rel="noopener noreferrer">
                                <div class="csmj-download-icon">
                                    <i class="bi bi-filetype-pdf"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1">Lista 1 — Escrivão de Direito</h6>
                                    <small class="text-muted">Classificação Final · PDF</small>
                                </div>
                                <i class="bi bi-download text-primary fs-5"></i>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="https://csmj.ao/storage/Document/6TlgukHOtUmU1JKlHTlRuA2i74b2RFNvUmG7UTkR.pdf" target="_blank" class="csmj-download-card" rel="noopener noreferrer">
                                <div class="csmj-download-icon">
                                    <i class="bi bi-filetype-pdf"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1">Lista 2 — Ajudante de Escrivão</h6>
                                    <small class="text-muted">Classificação Final · PDF</small>
                                </div>
                                <i class="bi bi-download text-primary fs-5"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Search Box -->
                <div class="csmj-search-section mb-4">
                    <div class="csmj-search-box">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-primary"></i></span>
                            <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Pesquisar pelo seu nome..." autocomplete="off">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small class="text-muted"><i class="bi bi-lightbulb me-1"></i> Dica: escreva pelo menos 3 letras do nome</small>
                        <small class="text-muted" id="resultCount"></small>
                    </div>
                </div>

                <!-- Cargo Filter -->
                <div class="mb-4">
                    <div class="d-flex flex-wrap gap-2" id="cargoFilters">
                        <button class="btn btn-sm btn-primary rounded-pill active" data-cargo="all">
                            <i class="bi bi-grid me-1"></i> Todos
                        </button>
                    </div>
                </div>

                <!-- Results Container -->
                <div id="resultsContainer">
                    <div class="text-center py-5" id="initialState">
                        <i class="bi bi-search fs-1 text-muted opacity-50"></i>
                        <p class="text-muted mt-3">Digite o seu nome para pesquisar os resultados</p>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="mt-5 pt-4 border-top" id="statsSection" style="display:none;">
                    <h4 class="fw-bold mb-3"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Estatísticas Gerais</h4>
                    <div class="row g-3" id="statsContainer"></div>
                </div>

                <div class="mt-5 pt-4 border-top">
                    <h4 class="fw-bold mb-3">Informações Adicionais</h4>
                    <p class="text-muted">Esta é uma <strong>lista provisória</strong>. Os candidatos poderão apresentar reclamações dentro dos prazos estipulados pelo CSMJ. Recomendamos que todos os candidatos consultem os canais oficiais do Conselho Superior da Magistratura Judicial para informações complementares.</p>
                    <p class="text-muted mb-0"><strong>Fonte:</strong> Conselho Superior da Magistratura Judicial (CSMJ) — Angola</p>
                </div>
            </div>

          </div>

          <div class="col-lg-4">
            <div class="sticky-top" style="top: 100px;">
                 <div class="card shadow-sm border-0 mb-4 rounded-3">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="fw-bold m-0 text-dark">Legenda dos Estados</h5>
                    </div>
                    <div class="card-body">
                         <div class="d-flex flex-column gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success rounded-pill px-3 py-2">Admitido</span>
                                <small class="text-muted">Aprovado e colocado</small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-warning text-dark rounded-pill px-3 py-2">Admitido não provido</span>
                                <small class="text-muted">Aprovado, aguarda vaga</small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-danger rounded-pill px-3 py-2">Não Admitido</span>
                                <small class="text-muted">Não aprovado</small>
                            </div>
                         </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 rounded-3">
                     <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="fw-bold m-0 text-dark">Artigos Relacionados</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="{{ url('/vagas') }}" class="list-group-item list-group-item-action py-3 border-0 border-bottom">
                            <h6 class="mb-1 fw-bold text-dark">Vagas de Emprego em Angola</h6>
                            <small class="text-muted">Veja todas as oportunidades</small>
                        </a>
                        <a href="{{ url('/cursos') }}" class="list-group-item list-group-item-action py-3 border-0 border-bottom">
                            <h6 class="mb-1 fw-bold text-dark">Cursos Gratuitos</h6>
                            <small class="text-muted">Desenvolva novas competências</small>
                        </a>
                    </div>
                </div>
            </div>
          </div>

        </div>
      </div>
    </section>
    
    @include('partials.social-cta')
    
    <style>
        .article-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 20px 0;
        }
        .article-content h2, .article-content h3 {
            font-weight: 700;
            margin-top: 2rem;
            color: #2d2d2d;
        }

        /* CSMJ Custom Styles */
        .csmj-search-box {
            background: linear-gradient(135deg, #eef2ff, #f0f4ff);
            border-radius: 12px;
            padding: 16px;
            border: 1px solid #dbeafe;
        }
        .csmj-search-box .form-control:focus {
            box-shadow: none;
        }
        .csmj-result-card {
            border: 1px solid #e8e8e8;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 8px;
            transition: all 0.2s ease;
            background: #fff;
        }
        .csmj-result-card:hover {
            border-color: #c7d2fe;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.08);
        }
        .csmj-result-card.status-admitido {
            border-left: 4px solid #22c55e;
        }
        .csmj-result-card.status-admitido-nao-provido {
            border-left: 4px solid #f59e0b;
        }
        .csmj-result-card.status-nao-admitido {
            border-left: 4px solid #ef4444;
        }
        .csmj-result-number {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
            color: #6b7280;
            flex-shrink: 0;
        }
        .csmj-nota {
            font-size: 1.1rem;
            font-weight: 700;
        }
        .csmj-nota.nota-alta { color: #22c55e; }
        .csmj-nota.nota-media { color: #f59e0b; }
        .csmj-nota.nota-baixa { color: #ef4444; }

        .csmj-cargo-badge {
            font-size: 0.7rem;
            padding: 3px 10px;
            border-radius: 50px;
            background: #e0e7ff;
            color: #3730a3;
            font-weight: 600;
        }

        .stat-card {
            background: #fff;
            border: 1px solid #e8e8e8;
            border-radius: 10px;
            padding: 16px;
            text-align: center;
        }
        .stat-card .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
        }
        .stat-card .stat-label {
            font-size: 0.8rem;
            color: #6b7280;
        }

        #cargoFilters .btn {
            font-size: 0.8rem;
        }
        #cargoFilters .btn:not(.active) {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #e5e7eb;
        }

        .loading-spinner {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }

        .csmj-download-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            border: 1px solid #e8e8e8;
            border-radius: 10px;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
            background: #fff;
        }
        .csmj-download-card:hover {
            border-color: #dc2626;
            box-shadow: 0 2px 12px rgba(220, 38, 38, 0.08);
            color: inherit;
            transform: translateY(-1px);
        }
        .csmj-download-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #dc2626;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
    </style>
@endsection

@section('footer-scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let allData = null;
    let allCandidatos = [];
    let activeCargo = 'all';
    let searchTimeout = null;

    // Load JSON
    fetch('/storage/classificacao_final.json')
        .then(r => r.json())
        .then(data => {
            allData = data;
            
            // Flatten all candidatos with cargo info
            data.cargos.forEach(cargo => {
                cargo.candidatos.forEach(c => {
                    allCandidatos.push({
                        n: c.n,
                        nome: c.nome,
                        p: c.p,
                        e: c.e,
                        cargo: cargo.cargo,
                        total: cargo.total
                    });
                });
            });

            // Build cargo filter buttons
            const filtersEl = document.getElementById('cargoFilters');
            data.cargos.forEach(cargo => {
                const btn = document.createElement('button');
                btn.className = 'btn btn-sm rounded-pill';
                btn.dataset.cargo = cargo.cargo;
                btn.innerHTML = `${cargo.cargo} <span class="badge bg-white text-dark ms-1">${cargo.total}</span>`;
                btn.addEventListener('click', function() {
                    filtersEl.querySelectorAll('.btn').forEach(b => b.classList.remove('btn-primary', 'active'));
                    this.classList.add('btn-primary', 'active');
                    activeCargo = this.dataset.cargo;
                    doSearch();
                });
                filtersEl.appendChild(btn);
            });

            // Build stats
            showStats(data);
        })
        .catch(err => {
            document.getElementById('resultsContainer').innerHTML = '<div class="alert alert-danger">Erro ao carregar os dados. Tente novamente.</div>';
        });

    // Search
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(doSearch, 200);
    });

    function doSearch() {
        const query = searchInput.value.trim().toLowerCase();
        const container = document.getElementById('resultsContainer');
        const countEl = document.getElementById('resultCount');

        if (query.length < 3) {
            container.innerHTML = `<div class="text-center py-5" id="initialState">
                <i class="bi bi-search fs-1 text-muted opacity-50"></i>
                <p class="text-muted mt-3">Digite pelo menos 3 letras para pesquisar</p>
            </div>`;
            countEl.textContent = '';
            return;
        }

        let results = allCandidatos.filter(c => c.nome.toLowerCase().includes(query));
        
        if (activeCargo !== 'all') {
            results = results.filter(c => c.cargo === activeCargo);
        }

        countEl.textContent = `${results.length} resultado(s) encontrado(s)`;

        if (results.length === 0) {
            container.innerHTML = `<div class="text-center py-5">
                <i class="bi bi-emoji-frown fs-1 text-muted opacity-50"></i>
                <p class="text-muted mt-3">Nenhum resultado encontrado para "<strong>${escapeHtml(searchInput.value)}</strong>"</p>
                <p class="text-muted small">Verifique a ortografia ou tente pesquisar por parte do nome</p>
            </div>`;
            return;
        }

        // Limit display to first 100 for performance
        const displayResults = results.slice(0, 100);
        let html = '';

        displayResults.forEach(c => {
            const statusClass = getStatusClass(c.e);
            const statusBadge = getStatusBadge(c.e);
            const notaClass = c.p >= 10 ? 'nota-alta' : (c.p >= 7 ? 'nota-media' : 'nota-baixa');
            const highlightedName = highlightMatch(c.nome, searchInput.value);

            html += `<div class="csmj-result-card ${statusClass}">
                <div class="d-flex align-items-center gap-3">
                    <div class="csmj-result-number">${c.n}</div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-1">${highlightedName}</h6>
                        <span class="csmj-cargo-badge">${c.cargo}</span>
                    </div>
                    <div class="text-end">
                        <div class="csmj-nota ${notaClass}">${c.p.toFixed(1)}</div>
                        ${statusBadge}
                    </div>
                </div>
            </div>`;
        });

        if (results.length > 100) {
            html += `<div class="text-center text-muted py-3">
                <small>Mostrando 100 de ${results.length} resultados. Refine a pesquisa para ver menos resultados.</small>
            </div>`;
        }

        container.innerHTML = html;
    }

    function getStatusClass(status) {
        if (status === 'Admitido') return 'status-admitido';
        if (status === 'Admitido não provido') return 'status-admitido-nao-provido';
        return 'status-nao-admitido';
    }

    function getStatusBadge(status) {
        if (status === 'Admitido') return '<span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1" style="font-size:0.7rem;">Admitido</span>';
        if (status === 'Admitido não provido') return '<span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-2 py-1" style="font-size:0.7rem;">Adm. não provido</span>';
        return '<span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1" style="font-size:0.7rem;">Não Admitido</span>';
    }

    function highlightMatch(text, query) {
        if (!query) return escapeHtml(text);
        const regex = new RegExp(`(${escapeRegex(query)})`, 'gi');
        return escapeHtml(text).replace(regex, '<mark>$1</mark>');
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function showStats(data) {
        const section = document.getElementById('statsSection');
        const container = document.getElementById('statsContainer');
        section.style.display = 'block';

        let totalCandidatos = 0;
        let totalAdmitidos = 0;
        let totalNaoProvidos = 0;
        let totalNaoAdmitidos = 0;

        data.cargos.forEach(cargo => {
            totalCandidatos += cargo.total;
            cargo.candidatos.forEach(c => {
                if (c.e === 'Admitido') totalAdmitidos++;
                else if (c.e === 'Admitido não provido') totalNaoProvidos++;
                else totalNaoAdmitidos++;
            });
        });

        container.innerHTML = `
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-number text-primary">${totalCandidatos.toLocaleString('pt-AO')}</div>
                    <div class="stat-label">Total Candidatos</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-number text-success">${totalAdmitidos.toLocaleString('pt-AO')}</div>
                    <div class="stat-label">Admitidos</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-number text-warning">${totalNaoProvidos.toLocaleString('pt-AO')}</div>
                    <div class="stat-label">Adm. não providos</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-number text-danger">${totalNaoAdmitidos.toLocaleString('pt-AO')}</div>
                    <div class="stat-label">Não Admitidos</div>
                </div>
            </div>
        `;
    }
});
</script>
@endsection

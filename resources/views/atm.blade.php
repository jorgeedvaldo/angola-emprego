@extends('templates.app')
@section('title', __('site.atm.meta_titulo'))
@section('description', __('site.atm.meta_descricao'))
@section('canonical_link', url('/atm-com-dinheiro'))

@section('content')
    <!-- Page Header -->
    <div class="bg-light py-5">
      <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                 <h1 class="fw-bold mb-2 text-dark">{{ __('site.atm.titulo') }}</h1>
                 <p class="text-muted mb-0">{{ __('site.atm.subtitulo') }}</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                 <nav aria-label="breadcrumb">
                  <ol class="breadcrumb justify-content-lg-end mb-0">
                    <li class="breadcrumb-item"><a href="{{url('/')}}">{{ __('site.atm.inicio') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('site.atm.atms') }}</li>
                  </ol>
                </nav>
            </div>
        </div>
      </div>
    </div>

    <section class="section py-5">
      <div class="container">
        
        <!-- Filters -->
        <div class="bg-white p-4 rounded-3 shadow-sm border mb-5">
            <div class="row g-3">
                <div class="col-lg-4">
                    <label class="form-label small fw-bold text-muted">{{ __('site.atm.pesquisar') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="search-input" class="form-control border-start-0 bg-light" placeholder="{{ __('site.atm.pesquisar_exemplo') }}">
                    </div>
                </div>
                <div class="col-lg-3">
                    <label class="form-label small fw-bold text-muted">{{ __('site.atm.provincia') }}</label>
                    <select class="form-select bg-light border-0" id="filter-province">
                        <option value="all">{{ __('site.atm.todas_provincias') }}</option>
                        <option value="Luanda" selected>Luanda</option>
                        <option value="Benguela">Benguela</option>
                        <option value="Huíla">Huíla</option>
                        <option value="Huambo">Huambo</option>
                        <option value="Cabinda">Cabinda</option>
                        <!-- Add other provinces as needed -->
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label small fw-bold text-muted">{{ __('site.atm.estado') }}</label>
                    <select class="form-select bg-light border-0" id="filter-status">
                        <option value="all" selected>{{ __('site.atm.todos_estados') }}</option>
                        <option value="com-dinheiro">{{ __('site.atm.com_dinheiro') }}</option>
                        <option value="sem-dinheiro">{{ __('site.atm.sem_dinheiro') }}</option>
                        <option value="indisponivel">{{ __('site.atm.indisponivel') }}</option>
                    </select>
                </div>
                 <div class="col-lg-2 d-flex align-items-end">
                    <button class="btn btn-outline-secondary w-100" id="clear-filters-btn">
                        <i class="bi bi-x-circle me-1"></i> {{ __('site.atm.limpar') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Loader -->
        <div id="loader" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">{{ __('site.atm.carregando') }}</span>
            </div>
            <p class="mt-3 text-muted">{{ __('site.atm.a_actualizar') }}</p>
        </div>

        <!-- Error Message -->
        <div id="error-message" class="alert alert-danger d-none text-center" role="alert"></div>

        <!-- Results Info -->
        <div class="d-flex justify-content-between align-items-center mb-4">
             <span id="results-count" class="badge bg-light text-dark border p-2">{{ __('site.atm.a_carregar') }}</span>
        </div>

        <!-- ATM Grid -->
        <div id="atm-results-container" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <!-- Cards Rendered via JS -->
        </div>

        <!-- No Results -->
        <div id="no-results-message" class="text-center py-5 d-none">
            <i class="bi bi-geo-alt-fill display-1 text-muted opacity-25"></i>
            <h5 class="mt-3 text-muted">{{ __('site.atm.sem_resultados') }}</h5>
            <p class="text-muted small">{{ __('site.atm.sem_resultados_dica') }}</p>
        </div>

      </div>
    </section>

    <script>
        const apiUrl = 'https://services.empregosyoyota.net/api/proxy/atms';

        // Textos vindos do servidor, no idioma escolhido pelo visitante: os cartões
        // são montados aqui no browser, por isso as frases têm de viajar com a página.
        @php
            $textos = [
                'com_dinheiro' => __('site.atm.com_dinheiro'),
                'sem_dinheiro' => __('site.atm.sem_dinheiro'),
                'fora_servico' => __('site.atm.fora_servico'),
                'desconhecido' => __('site.atm.desconhecido'),
                'encontrados' => __('site.atm.encontrados', ['count' => ':count']),
                'sem_endereco' => __('site.atm.sem_endereco'),
                'erro_carregar' => __('site.atm.erro_carregar'),
            ];
        @endphp
        const textos = @json($textos);

        // Elements
        const loader = document.getElementById('loader');
        const errorMessageDiv = document.getElementById('error-message');
        const atmResultsContainer = document.getElementById('atm-results-container');
        const noResultsMessage = document.getElementById('no-results-message');
        const resultsCountElement = document.getElementById('results-count');
        const searchInput = document.getElementById('search-input');
        const filterProvince = document.getElementById('filter-province');
        const filterStatus = document.getElementById('filter-status');
        const clearFiltersBtn = document.getElementById('clear-filters-btn');

        let allAtms = [];

        function getAtmStatusDetails(atm) {
            const statusCode = atm.atmStatus.code;
            let description = textos.desconhecido;
            let className = 'bg-secondary text-white';
            let icon = 'bi-question-circle';

            switch (statusCode) {
                case 4: case 2: // Money Available
                    description = textos.com_dinheiro;
                    className = 'bg-success text-white';
                    icon = 'bi-cash-coin';
                    break;
                case 3: case 1: // No Money
                    description = textos.sem_dinheiro;
                    className = 'bg-warning text-dark';
                    icon = 'bi-x-circle';
                    break;
                case 0: // Inactive
                    description = textos.fora_servico;
                    className = 'bg-danger text-white';
                    icon = 'bi-exclamation-triangle';
                    break;
            }
            return { description, className, icon };
        }

        function renderAtms(atms) {
            atmResultsContainer.innerHTML = '';
            resultsCountElement.textContent = textos.encontrados.replace(':count', atms.length);

            if (atms.length === 0) {
                noResultsMessage.classList.remove('d-none');
                return;
            }
            noResultsMessage.classList.add('d-none');

            const html = atms.map(atm => {
                const status = getAtmStatusDetails(atm);
                return `
                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm hover-shadow transition-all p-3" style="border-radius: 12px;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-center">
                                         <div class="bg-light p-2 rounded-circle me-3 text-primary">
                                            <i class="bi bi-credit-card-2-front fs-4"></i>
                                         </div>
                                         <div>
                                            <h6 class="fw-bold mb-0 text-dark text-truncate" style="max-width: 180px;">${atm.description || 'Multicaixa'}</h6>
                                            <small class="text-muted"><i class="bi bi-geo-alt me-1"></i> ${atm.province || 'N/A'}</small>
                                         </div>
                                    </div>
                                    <span class="badge ${status.className} rounded-pill small">
                                        <i class="bi ${status.icon} me-1"></i> ${status.description}
                                    </span>
                                </div>
                                <p class="card-text text-muted small mb-0 bg-light p-2 rounded">
                                    <i class="bi bi-signpost-2 me-1"></i> ${atm.street || textos.sem_endereco}
                                </p>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
            
            atmResultsContainer.innerHTML = html;
        }

        function filterAtms() {
            const term = searchInput.value.toLowerCase();
            const province = filterProvince.value;
            const statusType = filterStatus.value;

            const filtered = allAtms.filter(atm => {
                const matchesTerm = (atm.description?.toLowerCase().includes(term) || atm.street?.toLowerCase().includes(term));
                const matchesProvince = province === 'all' || atm.province === province;
                
                let matchesStatus = true;
                const code = atm.atmStatus.code;
                if (statusType === 'com-dinheiro') matchesStatus = (code === 4 || code === 2);
                if (statusType === 'sem-dinheiro') matchesStatus = (code === 3 || code === 1);
                if (statusType === 'indisponivel') matchesStatus = (code === 0);

                return matchesTerm && matchesProvince && matchesStatus;
            });

            renderAtms(filtered);
        }

        async function init() {
            try {
                const res = await fetch(apiUrl);
                if (!res.ok) throw new Error(res.status);
                const data = await res.json();
                allAtms = data.data.atmList.atmList || [];
                
                loader.classList.add('d-none');
                filterAtms(); // Initial render
            } catch (err) {
                loader.classList.add('d-none');
                errorMessageDiv.textContent = textos.erro_carregar;
                errorMessageDiv.classList.remove('d-none');
            }
        }

        // Events
        searchInput.addEventListener('input', filterAtms);
        filterProvince.addEventListener('change', filterAtms);
        filterStatus.addEventListener('change', filterAtms);
        clearFiltersBtn.addEventListener('click', () => {
            searchInput.value = '';
            filterProvince.value = 'Luanda';
            filterStatus.value = 'all';
            filterAtms();
        });

        document.addEventListener('DOMContentLoaded', init);
    </script>
    
    <style>
        .hover-shadow:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(0,0,0,.15)!important;
        }
        .transition-all {
            transition: all 0.3s ease;
        }
    </style>
@endsection('content')
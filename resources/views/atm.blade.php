@extends('templates.app')
@section('title', 'ATMs com Dinheiro Disponível em Angola')
@section('description', 'Encontre caixas automáticos (ATMs) com dinheiro disponível perto de si em Angola. Veja o estado e a localização dos ATMs em tempo real.')
@section('canonical_link', url('/atm-com-dinheiro'))

@section('content')
    <!-- Page Header -->
    <div class="bg-light py-5">
      <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                 <h1 class="fw-bold mb-2 text-dark">Localizador de ATMs</h1>
                 <p class="text-muted mb-0">Encontre caixas automáticos com dinheiro disponíveis em tempo real.</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                 <nav aria-label="breadcrumb">
                  <ol class="breadcrumb justify-content-lg-end mb-0">
                    <li class="breadcrumb-item"><a href="{{url('/')}}">Início</a></li>
                    <li class="breadcrumb-item active" aria-current="page">ATMs</li>
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
                    <label class="form-label small fw-bold text-muted">Pesquisar</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="search-input" class="form-control border-start-0 bg-light" placeholder="Nome ou endereço...">
                    </div>
                </div>
                <div class="col-lg-3">
                    <label class="form-label small fw-bold text-muted">Província</label>
                    <select class="form-select bg-light border-0" id="filter-province">
                        <option value="all">Todas as Províncias</option>
                        <option value="Luanda" selected>Luanda</option>
                        <option value="Benguela">Benguela</option>
                        <option value="Huíla">Huíla</option>
                        <option value="Huambo">Huambo</option>
                        <option value="Cabinda">Cabinda</option>
                        <!-- Add other provinces as needed -->
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label small fw-bold text-muted">Estado</label>
                    <select class="form-select bg-light border-0" id="filter-status">
                        <option value="all" selected>Todos os Estados</option>
                        <option value="com-dinheiro">Com Dinheiro</option>
                        <option value="sem-dinheiro">Sem Dinheiro</option>
                        <option value="indisponivel">Indisponível</option>
                    </select>
                </div>
                 <div class="col-lg-2 d-flex align-items-end">
                    <button class="btn btn-outline-secondary w-100" id="clear-filters-btn">
                        <i class="bi bi-x-circle me-1"></i> Limpar
                    </button>
                </div>
            </div>
        </div>

        <!-- Loader -->
        <div id="loader" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <p class="mt-3 text-muted">A atualizar dados dos ATMs...</p>
        </div>

        <!-- Error Message -->
        <div id="error-message" class="alert alert-danger d-none text-center" role="alert"></div>

        <!-- Results Info -->
        <div class="d-flex justify-content-between align-items-center mb-4">
             <span id="results-count" class="badge bg-light text-dark border p-2">A carregar...</span>
        </div>

        <!-- ATM Grid -->
        <div id="atm-results-container" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <!-- Cards Rendered via JS -->
        </div>

        <!-- No Results -->
        <div id="no-results-message" class="text-center py-5 d-none">
            <i class="bi bi-geo-alt-fill display-1 text-muted opacity-25"></i>
            <h5 class="mt-3 text-muted">Nenhum ATM encontrado</h5>
            <p class="text-muted small">Tente ajustar os filtros de pesquisa.</p>
        </div>

      </div>
    </section>

    <script>
        const apiUrl = 'https://services.empregosyoyota.net/api/proxy/atms';

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
            let description = 'Desconhecido';
            let className = 'bg-secondary text-white';
            let icon = 'bi-question-circle';

            switch (statusCode) {
                case 4: case 2: // Money Available
                    description = 'Com Dinheiro';
                    className = 'bg-success text-white';
                    icon = 'bi-cash-coin';
                    break;
                case 3: case 1: // No Money
                    description = 'Sem Dinheiro';
                    className = 'bg-warning text-dark';
                    icon = 'bi-x-circle';
                    break;
                case 0: // Inactive
                    description = 'Fora de Serviço';
                    className = 'bg-danger text-white';
                    icon = 'bi-exclamation-triangle';
                    break;
            }
            return { description, className, icon };
        }

        function renderAtms(atms) {
            atmResultsContainer.innerHTML = '';
            resultsCountElement.textContent = `${atms.length} ATMs encontrados`;

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
                                    <i class="bi bi-signpost-2 me-1"></i> ${atm.street || 'Endereço não disponível'}
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
                if (!res.ok) throw new Error('Falha na conexão');
                const data = await res.json();
                allAtms = data.data.atmList.atmList || [];
                
                loader.classList.add('d-none');
                filterAtms(); // Initial render
            } catch (err) {
                loader.classList.add('d-none');
                errorMessageDiv.textContent = 'Erro ao carregar ATMs. Tente novamente.';
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
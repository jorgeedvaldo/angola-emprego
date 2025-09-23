@extends('templates.app')
@section('title', 'ATMs com Dinheiro Disponível em Angola')
@section('description', 'Encontre caixas automáticos (ATMs) com dinheiro disponível perto de si em Angola. Veja o estado e a localização dos ATMs em tempo real.')
@section('canonical_link', url('/atm-com-dinheiro'))

@section('content')
<style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f4f7f9;
            color: #333;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .filters-container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            padding: 20px;
            margin-bottom: 25px;
        }
        .atm-card {
            background-color: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%; /* Garante que todos os cartões na linha tenham a mesma altura */
            display: flex;
            flex-direction: column;
        }
        .atm-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .atm-card h5 {
            margin-top: 0;
            font-size: 1.1em;
            color: #1a202c;
            margin-bottom: 10px;
        }
        .atm-card p {
            margin-bottom: 5px;
            font-size: 0.9em;
            color: #4a5568;
        }
        .atm-card .address {
            font-style: italic;
            font-size: 0.85em;
            color: #6c757d;
        }
        .atm-card .status-badge {
            margin-top: auto; /* Empurra o badge para o final do cartão */
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75em;
            font-weight: bold;
            color: #fff;
            text-transform: uppercase;
        }

        /* Cores dos badges baseadas no status */
        .status-badge-com-dinheiro { background-color: #28a745; } /* Verde */
        .status-badge-sem-dinheiro { background-color: #ffc107; color: #343a40; } /* Amarelo */
        .status-badge-indisponivel { background-color: #dc3545; } /* Vermelho */
        .status-badge-operacional { background-color: #007bff; } /* Azul - para status gerais como "com comprovativo", "sem comprovativo" */

        #loader, #error-message {
            text-align: center;
            padding: 50px;
            font-size: 1.1em;
        }
        #error-message {
            color: #dc3545;
        }
        .info-message {
            color: #6c757d;
            font-style: italic;
            text-align: center;
            padding: 20px;
        }
        .form-select, .form-control {
            border-radius: 0.375rem; /* Bootstrap default */
        }
        .btn-clear-filters {
            font-size: 0.9em;
            padding: 0.375rem 0.75rem;
        }
    </style>
<div class="container">
        <!-- Filtros e Barra de Pesquisa -->
        <div class="filters-container">
            <div class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-12">
                    <label for="search-input" class="form-label mb-1">Pesquisar (Nome, Endereço)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="search-input" placeholder="Ex: Multicaixa Benfica...">
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="filter-province" class="form-label mb-1">Província</label>
                    <select class="form-select" id="filter-province">
                        <option value="all" selected>Todas as Províncias</option>
                        <option value="Bengo">Bengo</option>
                        <option value="Benguela">Benguela</option>
                        <option value="Bié">Bié</option>
                        <option value="Cabinda">Cabinda</option>
                        <option value="Cuando Cubango">Cuando Cubango</option>
                        <option value="Cuanza Norte">Cuanza Norte</option>
                        <option value="Cuanza Sul">Cuanza Sul</option>
                        <option value="Cunene">Cunene</option>
                        <option value="Huambo">Huambo</option>
                        <option value="Huíla">Huíla</option>
                        <option value="Luanda">Luanda</option>
                        <option value="Lunda Norte">Lunda Norte</option>
                        <option value="Lunda Sul">Lunda Sul</option>
                        <option value="Malanje">Malanje</option>
                        <option value="Moxico">Moxico</option>
                        <option value="Namibe">Namibe</option>
                        <option value="Uíge">Uíge</option>
                        <option value="Zaire">Zaire</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="filter-status" class="form-label mb-1">Status do ATM</label>
                    <select class="form-select" id="filter-status">
                        <option value="all" selected>Todos</option>
                        <option value="com-dinheiro">Com Dinheiro</option>
                        <option value="sem-dinheiro">Sem Dinheiro</option>
                        <option value="com-comprovativo">Com Comprovativo</option>
                        <option value="sem-comprovativo">Sem Comprovativo</option>
                        <option value="indisponivel">Indisponível</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="sort-by" class="form-label mb-1">Ordenar por</label>
                    <select class="form-select" id="sort-by">
                        <option value="description_asc" selected>Alfabética (A-Z)</option>
                        <option value="province_asc">Província (A-Z)</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 d-grid">
                    <button class="btn btn-outline-secondary btn-clear-filters" id="clear-filters-btn">
                        <i class="fas fa-eraser me-1"></i>Limpar
                    </button>
                </div>
            </div>
        </div>

        <!-- Indicador de Carregamento -->
        <div id="loader">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">A carregar...</span>
            </div>
            <p class="mt-2">A carregar dados dos ATMs...</p>
        </div>

        <!-- Mensagem de erro -->
        <div id="error-message" class="info-message alert alert-danger" style="display:none;"></div>

        <!-- Contador de Resultados -->
        <p class="mb-3 text-muted" id="results-count" style="display:none;"></p>

        <!-- Container para os cartões dos ATMs -->
        <div id="atm-results-container" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <!-- ATMs serão renderizados aqui -->
        </div>

        <!-- Mensagem de Nenhhum Resultado -->
        <div id="no-results-message" class="info-message" style="display:none;">
            <p>Nenhum ATM encontrado com os filtros aplicados.</p>
        </div>

    </div>
        <!-- Bootstrap JS Bundle com Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXcWb5U9QvYfKxX2AnUjN5g8w4/nI8zVpB2T+yJd2i0NlEwK/L7V2sS" crossorigin="anonymous"></script>

    <script>
        const apiUrl = 'https://services.empregosyoyota.net/api/proxy/atms';

        // DOM Elements
        const loader = document.getElementById('loader');
        const errorMessageDiv = document.getElementById('error-message');
        const atmResultsContainer = document.getElementById('atm-results-container');
        const noResultsMessage = document.getElementById('no-results-message');
        const resultsCountElement = document.getElementById('results-count');

        // Filter Elements
        const searchInput = document.getElementById('search-input');
        const filterProvince = document.getElementById('filter-province'); // NOVO
        const filterStatus = document.getElementById('filter-status');
        const sortBy = document.getElementById('sort-by');
        const clearFiltersBtn = document.getElementById('clear-filters-btn');

        // Global data
        let allAtms = [];
        let filteredAndSortedAtms = [];

        // Helper function to get ATM status details
        function getAtmStatusDetails(atm) {
            const statusCode = atm.atmStatus.code;
            let description = atm.atmStatusDescription || 'Estado desconhecido';
            let className = 'status-badge-indisponivel';
            let icon = 'fas fa-exclamation-triangle'; // Default for unavailable

            switch (statusCode) {
                case 4: // ACTIVE_PAPER_AND_MONEY
                    description = 'Com Dinheiro e Comprovativo';
                    className = 'status-badge-com-dinheiro';
                    icon = 'fas fa-check-circle';
                    break;
                case 2: // ACTIVE_MONEY
                    description = 'Com Dinheiro (sem comprovativo)';
                    className = 'status-badge-com-dinheiro';
                    icon = 'fas fa-dollar-sign';
                    break;
                case 3: // ACTIVE_PAPER
                    description = 'Sem Dinheiro (com comprovativo)';
                    className = 'status-badge-sem-dinheiro';
                    icon = 'fas fa-receipt';
                    break;
                case 1: // ACTIVE
                    description = 'Sem Dinheiro e Sem Comprovativo';
                    className = 'status-badge-sem-dinheiro';
                    icon = 'fas fa-times-circle';
                    break;
                case 0: // INACTIVE
                    description = 'Indisponível';
                    className = 'status-badge-indisponivel';
                    icon = 'fas fa-ban';
                    break;
                default:
                    description = 'Estado Desconhecido';
                    className = 'status-badge-indisponivel';
                    icon = 'fas fa-question-circle';
            }
            return { description, className, icon };
        }

        // Function to render the ATM cards
        function renderAtms(atmsToDisplay) {
            atmResultsContainer.innerHTML = '';
            noResultsMessage.style.display = 'none';
            resultsCountElement.style.display = 'block';
            resultsCountElement.textContent = `Resultados encontrados: ${atmsToDisplay.length}`;

            if (atmsToDisplay.length === 0) {
                noResultsMessage.style.display = 'block';
                resultsCountElement.style.display = 'none';
                return;
            }

            atmsToDisplay.forEach(atm => {
                const status = getAtmStatusDetails(atm);
                const atmCardHtml = `
                    <div class="col">
                        <div class="atm-card">
                            <h5>${atm.description || 'ATM Desconhecido'}</h5>
                            <p class="address">${atm.street || 'Endereço não disponível'}</p>
                            <p class="address"><strong>${atm.province || 'Província não disponível'}</strong></p>
                            <span class="status-badge ${status.className}">
                                <i class="${status.icon} me-1"></i> ${status.description}
                            </span>
                        </div>
                    </div>
                `;
                atmResultsContainer.insertAdjacentHTML('beforeend', atmCardHtml);
            });
        }

        // Function to apply all filters and sorting
        function applyFiltersAndRender() {
            let tempAtms = [...allAtms]; // Start with all ATMs

            // 1. Apply Search Filter
            const searchTerm = searchInput.value.toLowerCase().trim();
            if (searchTerm) {
                tempAtms = tempAtms.filter(atm =>
                    (atm.description && atm.description.toLowerCase().includes(searchTerm)) ||
                    (atm.street && atm.street.toLowerCase().includes(searchTerm))
                );
            }

            // 2. Apply Province Filter (NOVO)
            const selectedProvince = filterProvince.value;
            if (selectedProvince !== 'all') {
                tempAtms = tempAtms.filter(atm => atm.province && atm.province === selectedProvince);
            }

            // 3. Apply Status Filter
            const selectedStatusFilter = filterStatus.value;
            if (selectedStatusFilter !== 'all') {
                tempAtms = tempAtms.filter(atm => {
                    const statusCode = atm.atmStatus.code;
                    switch (selectedStatusFilter) {
                        case 'com-dinheiro':
                            return statusCode === 4 || statusCode === 2; // Has money (with/without paper)
                        case 'sem-dinheiro':
                            return statusCode === 1 || statusCode === 3; // No money (with/without paper)
                        case 'com-comprovativo':
                            return statusCode === 4 || statusCode === 3; // Has paper (with/without money)
                        case 'sem-comprovativo':
                            return statusCode === 1 || statusCode === 2; // No paper (with/without money)
                        case 'indisponivel':
                            return statusCode === 0;
                        default:
                            return true;
                    }
                });
            }

            // 4. Apply Sorting
            const selectedSortBy = sortBy.value;
            tempAtms.sort((a, b) => {
                if (selectedSortBy === 'description_asc') {
                    const descA = a.description || '';
                    const descB = b.description || '';
                    return descA.localeCompare(descB);
                } else if (selectedSortBy === 'province_asc') {
                    const provA = a.province || '';
                    const provB = b.province || '';
                    if (provA.localeCompare(provB) !== 0) {
                        return provA.localeCompare(provB);
                    }
                    // Secondary sort by description if provinces are the same
                    const descA = a.description || '';
                    const descB = b.description || '';
                    return descA.localeCompare(descB);
                }
                return 0;
            });

            filteredAndSortedAtms = tempAtms;
            renderAtms(filteredAndSortedAtms);
        }

        // Function to clear all filters
        function clearAllFilters() {
            searchInput.value = '';
            filterProvince.value = 'all'; // NOVO
            filterStatus.value = 'all';
            sortBy.value = 'description_asc';
            applyFiltersAndRender();
        }

        // Main function to fetch data
        async function fetchAndDisplayAtms() {
            loader.style.display = 'block';
            errorMessageDiv.style.display = 'none';
            atmResultsContainer.innerHTML = '';
            noResultsMessage.style.display = 'none';
            resultsCountElement.style.display = 'none';

            try {
                const response = await fetch(apiUrl);

                if (!response.ok) {
                    throw new Error(`Erro na rede: ${response.status} - ${response.statusText}`);
                }

                const data = await response.json();
                allAtms = data.data.atmList.atmList || [];

                if (allAtms.length === 0) {
                    throw new Error('Nenhum ATM encontrado na resposta da API.');
                }
                
                applyFiltersAndRender();

            } catch (error) {
                console.error('Falha ao buscar dados da API:', error);
                errorMessageDiv.style.display = 'block';
                errorMessageDiv.textContent = `Ocorreu um erro ao carregar os dados. Por favor, tente novamente mais tarde. (Detalhe: ${error.message})`;
                noResultsMessage.style.display = 'none';
            } finally {
                loader.style.display = 'none';
            }
        }

        // Event Listeners
        searchInput.addEventListener('input', applyFiltersAndRender);
        filterProvince.addEventListener('change', applyFiltersAndRender); // NOVO
        filterStatus.addEventListener('change', applyFiltersAndRender);
        sortBy.addEventListener('change', applyFiltersAndRender);
        clearFiltersBtn.addEventListener('click', clearAllFilters);

        // Initial fetch on page load
        document.addEventListener('DOMContentLoaded', fetchAndDisplayAtms);
    </script>
@endsection('content')
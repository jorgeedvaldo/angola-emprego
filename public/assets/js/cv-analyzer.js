/**
 * Analisador de CVs público.
 *
 * Os ficheiros escolhidos nunca são guardados: ficam no browser, cada um é
 * enviado uma única vez para obter a pontuação e a lista é reordenada aqui
 * mesmo, sem recarregar a página nem gravar nada no servidor.
 */
(function () {
    const config = window.CV_ANALYZER_CONFIG;

    if (!config) {
        return;
    }

    /**
     * Texto no idioma do visitante, vindo do Blade. O fallback para a chave só
     * aparece se alguém acrescentar uma mensagem aqui e se esquecer de a pôr nos
     * ficheiros de tradução — é feio, mas é visível, que é o que interessa.
     */
    function t(chave, valores) {
        const strings = config.strings || {};
        let texto = strings[chave] !== undefined ? strings[chave] : chave;

        for (const nome in valores || {}) {
            texto = texto.replace(':' + nome, valores[nome]);
        }

        return texto;
    }

    const elements = {
        form: document.getElementById('cv-analyzer-form'),
        title: document.getElementById('cv-analyzer-title'),
        description: document.getElementById('cv-analyzer-description'),
        input: document.getElementById('cv-analyzer-files'),
        button: document.getElementById('cv-analyzer-start'),
        results: document.getElementById('cv-analyzer-results'),
        empty: document.getElementById('cv-analyzer-empty'),
        count: document.getElementById('cv-analyzer-count'),
        alert: document.getElementById('cv-analyzer-alert'),
        progressWrap: document.getElementById('cv-analyzer-progress-wrap'),
        progressBar: document.getElementById('cv-analyzer-progress-bar'),
        status: document.getElementById('cv-analyzer-status'),
        requirementsRead: document.getElementById('cv-analyzer-requirements'),
    };

    if (!elements.form || !elements.input || !elements.results) {
        return;
    }

    /** @type {{file: File, name: string, score: number|null, matched: string[], requirements: object[], error: string|null}[]} */
    let entries = [];
    let running = false;

    function csrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');

        return meta ? meta.getAttribute('content') : '';
    }

    function showAlert(message) {
        if (!message) {
            elements.alert.classList.add('d-none');
            elements.alert.textContent = '';

            return;
        }

        elements.alert.textContent = message;
        elements.alert.classList.remove('d-none');
    }

    function scoreBadge(entry) {
        if (entry.error) {
            return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-2">' + t('falhou') + '</span>';
        }

        if (entry.score === null || entry.score === undefined) {
            return '<span class="badge bg-light text-muted border ms-2">' + t('por_analisar') + '</span>';
        }

        const percent = Math.round(Math.max(0, entry.score) * 100);
        const style = entry.score >= 0.5 ? 'bg-success' : (entry.score >= 0.3 ? 'bg-warning text-dark' : 'bg-secondary');

        return '<span class="badge ' + style + ' ms-2">' + t('compativel', { percent: percent }) + '</span>';
    }

    function escapeHtml(text) {
        const element = document.createElement('span');
        element.textContent = text;

        return element.innerHTML;
    }

    const STATUS = {
        cumpre: { icon: 'bi-check-circle-fill', color: 'text-success', chave: 'cumpre' },
        parcial: { icon: 'bi-dash-circle-fill', color: 'text-warning', chave: 'parcial' },
        ausente: { icon: 'bi-x-circle-fill', color: 'text-secondary', chave: 'ausente' },
    };

    /**
     * A checklist é o que torna a pontuação explicável: em vez de uma percentagem
     * sozinha, o recrutador vê que requisitos é que este CV responde.
     */
    function requirementList(entry) {
        if (!entry.requirements || !entry.requirements.length) {
            return '';
        }

        const rows = entry.requirements.map((requirement) => {
            const status = STATUS[requirement.status] || STATUS.ausente;
            const terms = requirement.matched && requirement.matched.length
                ? ' <span class="text-muted">— ' + escapeHtml(requirement.matched.join(', ')) + '</span>'
                : '';
            // Um diferencial em falta não é o mesmo que uma exigência em falta, e
            // o recrutador tem de ver essa diferença na lista.
            const optional = requirement.optional
                ? ' <span class="badge bg-light text-muted border fw-normal">' + t('diferencial') + '</span>'
                : '';

            return '<li class="mb-1"><i class="bi ' + status.icon + ' ' + status.color + ' me-1"></i>'
                + escapeHtml(requirement.text)
                + ' <span class="' + status.color + '">(' + t(status.chave) + ')</span>'
                + optional
                + terms
                + '</li>';
        }).join('');

        return '<ul class="list-unstyled small mt-3 mb-0 border-top pt-3">' + rows + '</ul>';
    }

    /** Mostra ao recrutador que requisitos é que lemos do anúncio dele. */
    function showRequirementsRead(requirements) {
        if (!elements.requirementsRead) {
            return;
        }

        if (!requirements.length) {
            elements.requirementsRead.classList.add('d-none');
            elements.requirementsRead.innerHTML = '';

            return;
        }

        elements.requirementsRead.innerHTML =
            '<div class="small fw-semibold mb-2">' + t('requisitos_lidos') + '</div>'
            + '<ol class="small text-muted mb-0 ps-3">'
            + requirements.map((requirement) => '<li>' + escapeHtml(requirement.text) + '</li>').join('')
            + '</ol>';
        elements.requirementsRead.classList.remove('d-none');
    }

    function render() {
        elements.count.textContent = entries.length
            ? entries.length + ' ' + (entries.length === 1 ? t('escolhidos_um') : t('escolhidos_varios'))
            : '';
        elements.empty.classList.toggle('d-none', entries.length > 0);
        elements.results.innerHTML = '';

        // Analisados primeiro (do mais para o menos compatível) e, a seguir, os
        // que ainda não têm pontuação, pela ordem em que foram escolhidos.
        const ordered = entries.slice().sort((a, b) => {
            const scoreA = a.score === null || a.score === undefined ? -2 : a.score;
            const scoreB = b.score === null || b.score === undefined ? -2 : b.score;

            return scoreB - scoreA;
        });

        ordered.forEach((entry, position) => {
            const card = document.createElement('div');
            card.className = 'card border-0 shadow-sm mb-3';
            card.style.borderRadius = '12px';

            const matched = entry.matched.length
                ? '<div class="small text-muted mt-1"><i class="bi bi-check2-circle text-success"></i> '
                    + t('termos_encontrados') + ' ' + escapeHtml(entry.matched.join(', ')) + '</div>'
                : '';

            const checklist = requirementList(entry);

            const error = entry.error
                ? '<div class="small text-danger mt-1">' + escapeHtml(entry.error) + '</div>'
                : '';

            card.innerHTML =
                '<div class="card-body p-3 p-md-4">'
                + '<div class="d-flex flex-wrap justify-content-between align-items-start gap-2">'
                + '<div class="me-auto">'
                + '<h6 class="fw-bold mb-0"><span class="text-muted me-1">' + (position + 1) + '.</span>'
                + escapeHtml(entry.name) + scoreBadge(entry) + '</h6>'
                + matched
                + error
                + checklist
                + '</div>'
                + '<div class="d-flex gap-2">'
                + '<button type="button" class="btn btn-sm btn-outline-primary" data-open>'
                + '<i class="bi bi-eye me-1"></i> ' + t('abrir') + '</button>'
                + '<button type="button" class="btn btn-sm btn-outline-danger" data-remove>'
                + '<i class="bi bi-x-lg"></i></button>'
                + '</div>'
                + '</div>'
                + '</div>';

            card.querySelector('[data-open]').addEventListener('click', () => {
                // O ficheiro já está no browser: abre-se a partir da memória, sem
                // passar pelo servidor.
                const url = URL.createObjectURL(entry.file);
                window.open(url, '_blank');
                setTimeout(() => URL.revokeObjectURL(url), 60000);
            });

            card.querySelector('[data-remove]').addEventListener('click', () => {
                if (running) {
                    return;
                }

                entries = entries.filter((candidate) => candidate !== entry);
                render();
            });

            elements.results.appendChild(card);
        });
    }

    function addFiles(fileList) {
        const files = Array.from(fileList || []);
        let rejected = 0;

        files.forEach((file) => {
            if (entries.length >= config.maxFiles) {
                rejected += 1;

                return;
            }

            if (!/\.pdf$/i.test(file.name)) {
                rejected += 1;

                return;
            }

            const duplicated = entries.some(
                (entry) => entry.file.name === file.name && entry.file.size === file.size
            );

            if (duplicated) {
                return;
            }

            entries.push({ file, name: file.name, score: null, matched: [], requirements: [], error: null });
        });

        if (rejected) {
            showAlert(t('ignorados', { count: rejected, max: config.maxFiles }));
        }

        render();
    }

    async function postJson(url, body) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(body),
        });

        const data = await response.json().catch(() => null);

        if (!response.ok || !data || data.ok !== true) {
            throw new Error(messageFrom(data, response.status));
        }

        return data;
    }

    async function postFile(url, formData) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        });

        const data = await response.json().catch(() => null);

        if (!response.ok || !data || data.ok !== true) {
            throw new Error(messageFrom(data, response.status));
        }

        return data;
    }

    function messageFrom(data, status) {
        if (data && data.message) {
            return data.message;
        }

        if (data && data.errors) {
            const first = Object.values(data.errors)[0];

            if (Array.isArray(first) && first.length) {
                return first[0];
            }
        }

        if (status === 429) {
            return t('demasiados_pedidos');
        }

        return t('pedido_falhou', { status: status });
    }

    async function run() {
        if (running) {
            return;
        }

        showAlert('');

        const description = elements.description.value.trim();

        if (description.length < 30) {
            showAlert(t('descricao_curta'));
            elements.description.focus();

            return;
        }

        if (!entries.length) {
            showAlert(t('sem_ficheiros'));

            return;
        }

        running = true;
        elements.button.disabled = true;
        elements.progressWrap.classList.remove('d-none');
        elements.progressBar.style.width = '0%';
        elements.status.textContent = t('a_analisar_vaga');

        try {
            const job = await postJson(config.jobUrl, {
                title: elements.title ? elements.title.value.trim() : '',
                description,
            });
            const vector = JSON.stringify(job.vector);
            const keywords = JSON.stringify(job.keywords || []);
            const requirements = JSON.stringify(job.requirements || []);

            showRequirementsRead(job.requirements || []);

            // Uma nova descrição invalida as pontuações anteriores.
            entries.forEach((entry) => {
                entry.score = null;
                entry.matched = [];
                entry.requirements = [];
                entry.error = null;
            });
            render();

            let done = 0;
            let failures = 0;

            for (const entry of entries) {
                elements.status.textContent = t('a_analisar_cv', { nome: entry.name });

                const formData = new FormData();
                formData.append('cv', entry.file, entry.file.name);
                formData.append('vector', vector);
                formData.append('model', job.model);
                formData.append('keywords', keywords);
                formData.append('requirements', requirements);

                try {
                    const result = await postFile(config.cvUrl, formData);
                    entry.score = typeof result.score === 'number' ? result.score : null;
                    entry.matched = result.matched || [];
                    entry.requirements = result.requirements || [];
                } catch (error) {
                    failures += 1;
                    entry.error = error.message;
                }

                done += 1;
                elements.progressBar.style.width = Math.round((done / entries.length) * 100) + '%';
                render();
            }

            elements.status.textContent = failures
                ? t('concluida_com_erros', { count: failures })
                : t('concluida');
        } catch (error) {
            elements.status.textContent = '';
            showAlert(error.message);
        }

        running = false;
        elements.button.disabled = false;
    }

    elements.input.addEventListener('change', (event) => {
        showAlert('');
        addFiles(event.target.files);
        // Permite voltar a escolher o mesmo ficheiro depois de o remover da lista.
        event.target.value = '';
    });

    elements.form.addEventListener('submit', (event) => {
        event.preventDefault();
        run();
    });

    render();
})();

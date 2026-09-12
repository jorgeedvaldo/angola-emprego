(function () {
    function csrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    async function postJson(config, url) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const data = await response.json().catch(() => null);

        if (!response.ok || !data || data.ok !== true) {
            throw new Error((data && data.message) || t(config, 'js_pedido_falhou', { status: response.status }));
        }

        return data;
    }

    /**
     * Texto no idioma do visitante, vindo do Blade — o JavaScript não tem acesso
     * aos ficheiros de tradução.
     */
    function t(config, chave, valores) {
        const strings = (config && config.strings) || {};
        let texto = strings[chave] !== undefined ? strings[chave] : chave;

        for (const nome in valores || {}) {
            texto = texto.replace(':' + nome, valores[nome]);
        }

        return texto;
    }

    async function run(config, elements) {
        elements.button.disabled = true;
        elements.progressWrap.classList.remove('d-none');

        const pending = config.applications.filter((application) => !application.hasVector);
        let done = 0;
        let failures = 0;

        try {
            if (!config.job.hasVector) {
                elements.status.textContent = t(config, 'js_a_analisar_vaga');
                await postJson(config, config.job.analyzeUrl);
            }

            for (const application of pending) {
                elements.status.textContent = t(config, 'js_a_analisar_cv', { nome: application.name });

                try {
                    await postJson(config, application.analyzeUrl);
                } catch (error) {
                    failures += 1;
                    console.error('Falha ao analisar candidatura', application.id, error);
                }

                done += 1;
                const percent = pending.length ? Math.round((done / pending.length) * 100) : 100;
                elements.progressBar.style.width = percent + '%';
            }

            elements.status.textContent = failures
                ? t(config, 'js_concluida_com_erros', { count: failures })
                : t(config, 'js_concluida');

            window.location.reload();
        } catch (error) {
            elements.status.textContent = t(config, 'js_erro', { mensagem: error.message });
            elements.button.disabled = false;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const config = window.CV_ANALYSIS_CONFIG;
        const button = document.getElementById('cv-analysis-start');

        if (!config || !button) {
            return;
        }

        const elements = {
            button,
            status: document.getElementById('cv-analysis-status'),
            progressWrap: document.getElementById('cv-analysis-progress-wrap'),
            progressBar: document.getElementById('cv-analysis-progress-bar'),
        };

        button.addEventListener('click', () => run(config, elements));
    });
})();

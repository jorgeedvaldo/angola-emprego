(function () {
    function csrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    async function postJson(url) {
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
            throw new Error((data && data.message) || 'O pedido ao servidor falhou (' + response.status + ').');
        }

        return data;
    }

    async function run(config, elements) {
        elements.button.disabled = true;
        elements.progressWrap.classList.remove('d-none');

        const pending = config.applications.filter((application) => !application.hasVector);
        let done = 0;
        let failures = 0;

        try {
            if (!config.job.hasVector) {
                elements.status.textContent = 'A analisar a descrição da vaga…';
                await postJson(config.job.analyzeUrl);
            }

            for (const application of pending) {
                elements.status.textContent = 'A analisar o CV de ' + application.name + '…';

                try {
                    await postJson(application.analyzeUrl);
                } catch (error) {
                    failures += 1;
                    console.error('Falha ao analisar candidatura', application.id, error);
                }

                done += 1;
                const percent = pending.length ? Math.round((done / pending.length) * 100) : 100;
                elements.progressBar.style.width = percent + '%';
            }

            elements.status.textContent = failures
                ? 'Análise concluída com ' + failures + ' erro(s). A actualizar a lista…'
                : 'Análise concluída. A actualizar a lista…';

            window.location.reload();
        } catch (error) {
            elements.status.textContent = 'Erro: ' + error.message;
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

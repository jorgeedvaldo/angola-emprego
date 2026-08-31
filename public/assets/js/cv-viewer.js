(function () {
    let pdfJsPromise = null;

    function loadPdfJs(config) {
        if (pdfJsPromise) {
            return pdfJsPromise;
        }

        pdfJsPromise = new Promise((resolve, reject) => {
            if (window.pdfjsLib) {
                resolve();
                return;
            }

            const script = document.createElement('script');
            script.src = config.pdfJsUrl;
            script.onload = () => resolve();
            script.onerror = () => reject(new Error('Falha ao carregar o leitor de PDF.'));
            document.head.appendChild(script);
        }).then(() => {
            window.pdfjsLib.GlobalWorkerOptions.workerSrc = config.pdfWorkerUrl;
        });

        return pdfJsPromise;
    }

    function showMessage(container, text, isError) {
        container.innerHTML = '';
        const message = document.createElement('div');
        message.className = 'text-center py-5 ' + (isError ? 'text-danger' : 'text-muted');
        message.textContent = text;
        container.appendChild(message);
    }

    async function renderPdf(config, url, container) {
        showMessage(container, 'A carregar CV…', false);

        let requestToken;
        container.dataset.renderToken = requestToken = String(Date.now() + Math.random());

        try {
            await loadPdfJs(config);

            const response = await fetch(url);
            if (!response.ok) {
                throw new Error('Não foi possível obter o ficheiro (' + response.status + ').');
            }

            const buffer = await response.arrayBuffer();
            const doc = await window.pdfjsLib.getDocument({ data: buffer }).promise;

            // The viewer may have been reopened for another CV while this one was loading.
            if (container.dataset.renderToken !== requestToken) {
                return;
            }

            container.innerHTML = '';

            for (let i = 1; i <= doc.numPages; i++) {
                const page = await doc.getPage(i);
                const viewport = page.getViewport({ scale: 1.4 });
                const canvas = document.createElement('canvas');
                canvas.className = 'border shadow-sm mb-3 mx-auto d-block bg-white';
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                canvas.style.maxWidth = '100%';
                canvas.style.height = 'auto';
                container.appendChild(canvas);

                const context = canvas.getContext('2d');
                await page.render({ canvasContext: context, viewport }).promise;

                if (container.dataset.renderToken !== requestToken) {
                    return;
                }
            }
        } catch (error) {
            if (container.dataset.renderToken === requestToken) {
                showMessage(
                    container,
                    'Não foi possível pré-visualizar este CV. Use o botão de download. (' + error.message + ')',
                    true
                );
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const config = window.CV_VIEWER_CONFIG;
        const container = document.getElementById('cv-viewer-body');
        const titleEl = document.getElementById('cv-viewer-title');

        if (!config || !container) {
            return;
        }

        document.querySelectorAll('[data-cv-preview-url]').forEach((button) => {
            button.addEventListener('click', () => {
                const url = button.getAttribute('data-cv-preview-url');
                const name = button.getAttribute('data-cv-preview-name') || 'CV';
                if (titleEl) {
                    titleEl.textContent = name;
                }
                renderPdf(config, url, container);
            });
        });
    });
})();

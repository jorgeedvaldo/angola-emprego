(function () {
    // Multilingual (covers Portuguese) — must match VectorSimilarity::MODEL_ID in
    // app/Support/VectorSimilarity.php. all-MiniLM-L6-v2 is English-only and scores
    // unrelated Portuguese text as artificially similar.
    const MODEL_ID = 'Xenova/paraphrase-multilingual-MiniLM-L12-v2';
    const MIN_TEXT_LENGTH_BEFORE_OCR = 40;
    const MAX_TEXT_LENGTH = 8000;
    const OCR_LANGUAGE = 'por';

    let embedder = null;
    const loadedScripts = {};

    function loadScript(src) {
        if (loadedScripts[src]) {
            return loadedScripts[src];
        }

        loadedScripts[src] = new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            script.onload = () => resolve();
            script.onerror = () => reject(new Error('Falha ao carregar ' + src));
            document.head.appendChild(script);
        });

        return loadedScripts[src];
    }

    function csrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    async function postJson(url, payload) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        });

        if (!response.ok) {
            throw new Error('O pedido ao servidor falhou (' + response.status + ').');
        }

        return response.json();
    }

    async function getEmbedder(config) {
        if (embedder) {
            return embedder;
        }

        const transformers = await import(config.transformersUrl);
        embedder = await transformers.pipeline('feature-extraction', MODEL_ID, {
            quantized: true,
        });

        return embedder;
    }

    async function embed(config, text) {
        const model = await getEmbedder(config);
        const output = await model(text, { pooling: 'mean', normalize: true });
        return Array.from(output.data);
    }

    async function extractPdfText(config, arrayBuffer) {
        await loadScript(config.pdfJsUrl);
        window.pdfjsLib.GlobalWorkerOptions.workerSrc = config.pdfWorkerUrl;

        const doc = await window.pdfjsLib.getDocument({ data: arrayBuffer }).promise;
        let text = '';
        const pages = [];

        for (let i = 1; i <= doc.numPages; i++) {
            const page = await doc.getPage(i);
            const content = await page.getTextContent();
            text += content.items.map((item) => item.str).join(' ').trim() + '\n';
            pages.push(page);
        }

        return { text: text.trim(), pages };
    }

    async function ocrPages(config, pages) {
        await loadScript(config.tesseractUrl);
        const worker = await window.Tesseract.createWorker(OCR_LANGUAGE);

        let text = '';
        try {
            for (const page of pages) {
                const viewport = page.getViewport({ scale: 2 });
                const canvas = document.createElement('canvas');
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                const context = canvas.getContext('2d');
                await page.render({ canvasContext: context, viewport }).promise;

                const result = await worker.recognize(canvas);
                text += result.data.text + '\n';
            }
        } finally {
            await worker.terminate();
        }

        return text.trim();
    }

    async function extractTextFromCv(config, url) {
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error('Não foi possível obter o ficheiro do CV.');
        }

        const buffer = await response.arrayBuffer();
        const { text, pages } = await extractPdfText(config, buffer);

        if (text.length >= MIN_TEXT_LENGTH_BEFORE_OCR) {
            return text.slice(0, MAX_TEXT_LENGTH);
        }

        const ocrText = await ocrPages(config, pages);
        return ocrText.slice(0, MAX_TEXT_LENGTH);
    }

    async function ensureJobVector(config, statusEl) {
        if (config.job.hasVector) {
            return;
        }

        statusEl.textContent = 'A gerar o vector da descrição da vaga…';
        const vector = await embed(config, config.job.descriptionText);
        await postJson(config.job.vectorUrl, { vector, model: MODEL_ID });
        config.job.hasVector = true;
    }

    async function analyzeApplication(config, application, statusEl) {
        statusEl.textContent = 'A ler o CV de ' + application.name + '…';
        const text = await extractTextFromCv(config, application.downloadUrl);

        if (!text) {
            throw new Error('Não foi possível extrair texto deste CV.');
        }

        statusEl.textContent = 'A calcular compatibilidade — ' + application.name + '…';
        const vector = await embed(config, text);
        await postJson(application.vectorUrl, { text, vector, model: MODEL_ID });
    }

    async function run(config, elements) {
        elements.button.disabled = true;
        elements.progressWrap.classList.remove('d-none');

        const pending = config.applications.filter((application) => !application.hasVector);
        let done = 0;
        let failures = 0;

        try {
            await ensureJobVector(config, elements.status);

            for (const application of pending) {
                try {
                    await analyzeApplication(config, application, elements.status);
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

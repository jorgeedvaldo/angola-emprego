import { createRequire } from 'node:module';
import path from 'node:path';
import { createCanvas } from 'canvas';
import * as pdfjsLib from 'pdfjs-dist/legacy/build/pdf.mjs';
import { createWorker } from 'tesseract.js';
import { config } from './config.js';

const require = createRequire(import.meta.url);
const pdfjsDistDir = path.dirname(require.resolve('pdfjs-dist/package.json'));
const standardFontDataUrl = path.join(pdfjsDistDir, 'standard_fonts') + path.sep;
const cMapUrl = path.join(pdfjsDistDir, 'cmaps') + path.sep;

// pdf.js needs a way to create canvases when rendering pages; the browser has
// document.createElement('canvas'), Node doesn't, so we provide one backed by
// the `canvas` package. See pdfjs-dist's own Node.js rendering examples.
class NodeCanvasFactory {
    create(width, height) {
        const canvas = createCanvas(width, height);
        return { canvas, context: canvas.getContext('2d') };
    }

    reset(canvasAndContext, width, height) {
        canvasAndContext.canvas.width = width;
        canvasAndContext.canvas.height = height;
    }

    destroy(canvasAndContext) {
        canvasAndContext.canvas.width = 0;
        canvasAndContext.canvas.height = 0;
        canvasAndContext.canvas = null;
        canvasAndContext.context = null;
    }
}

async function loadDocument(buffer) {
    const canvasFactory = new NodeCanvasFactory();
    const doc = await pdfjsLib.getDocument({
        data: new Uint8Array(buffer),
        disableWorker: true,
        standardFontDataUrl,
        cMapUrl,
        cMapPacked: true,
        canvasFactory,
    }).promise;

    return { doc, canvasFactory };
}

async function extractTextLayer(doc, numPages) {
    let text = '';

    for (let i = 1; i <= numPages; i++) {
        const page = await doc.getPage(i);
        const content = await page.getTextContent();
        text += content.items.map((item) => item.str).join(' ').trim() + '\n';
    }

    return text.trim();
}

async function ocrDocument(doc, numPages, canvasFactory) {
    const worker = await createWorker(config.ocrLanguage);
    let text = '';

    try {
        for (let i = 1; i <= numPages; i++) {
            const page = await doc.getPage(i);
            const viewport = page.getViewport({ scale: 2 });
            const canvasAndContext = canvasFactory.create(viewport.width, viewport.height);

            await page.render({ canvasContext: canvasAndContext.context, viewport }).promise;

            const { data } = await worker.recognize(canvasAndContext.canvas.toBuffer('image/png'));
            text += data.text + '\n';

            canvasFactory.destroy(canvasAndContext);
        }
    } finally {
        await worker.terminate();
    }

    return text.trim();
}

export async function extractCvText(buffer) {
    const { doc, canvasFactory } = await loadDocument(buffer);
    const numPages = Math.min(doc.numPages, config.maxPdfPages);

    const textLayer = await extractTextLayer(doc, numPages);

    if (textLayer.length >= config.minTextLengthBeforeOcr) {
        return { text: textLayer.slice(0, config.maxTextLength), usedOcr: false, numPages };
    }

    const ocrText = await ocrDocument(doc, numPages, canvasFactory);
    return { text: ocrText.slice(0, config.maxTextLength), usedOcr: true, numPages };
}

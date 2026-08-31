import 'dotenv/config';

function parseList(value) {
    return (value || '')
        .split(',')
        .map((item) => item.trim())
        .filter(Boolean);
}

export const config = {
    port: parseInt(process.env.PORT || '3000', 10),
    apiKey: process.env.API_KEY || '',
    allowedOrigins: parseList(process.env.ALLOWED_ORIGINS),
    modelId: process.env.MODEL_ID || 'Xenova/paraphrase-multilingual-MiniLM-L12-v2',
    modelCacheDir: process.env.MODEL_CACHE_DIR || './model-cache',
    ocrLanguage: process.env.OCR_LANGUAGE || 'por',
    maxTextLength: parseInt(process.env.MAX_TEXT_LENGTH || '8000', 10),
    minTextLengthBeforeOcr: parseInt(process.env.MIN_TEXT_LENGTH_BEFORE_OCR || '40', 10),
    maxUploadBytes: parseInt(process.env.MAX_UPLOAD_BYTES || String(10 * 1024 * 1024), 10),
    maxPdfPages: parseInt(process.env.MAX_PDF_PAGES || '15', 10),
};

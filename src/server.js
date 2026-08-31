import cors from 'cors';
import express from 'express';
import rateLimit from 'express-rate-limit';
import multer from 'multer';
import { config } from './config.js';
import { requireApiKey } from './auth.js';
import { embed, warmUpEmbedder, MODEL_ID } from './embedding.js';
import { extractCvText } from './extraction.js';

// tesseract.js can surface worker/network errors (e.g. failing to download
// language data) as raw uncaught exceptions instead of a rejected promise,
// which would otherwise crash this whole service for every in-flight and
// future request over one bad CV. Log and keep serving instead.
process.on('uncaughtException', (error) => {
    console.error('uncaughtException (processo mantido vivo):', error);
});
process.on('unhandledRejection', (error) => {
    console.error('unhandledRejection (processo mantido vivo):', error);
});

const app = express();
app.disable('x-powered-by');
app.use(express.json({ limit: '1mb' }));

const corsOptions = config.allowedOrigins.length
    ? { origin: config.allowedOrigins }
    : {};
app.use(cors(corsOptions));

const limiter = rateLimit({
    windowMs: 60 * 1000,
    limit: 30,
    standardHeaders: true,
    legacyHeaders: false,
});
app.use(limiter);

const upload = multer({
    storage: multer.memoryStorage(),
    limits: { fileSize: config.maxUploadBytes },
});

app.get('/health', (req, res) => {
    res.json({ ok: true, model: MODEL_ID });
});

app.post('/embed', requireApiKey, async (req, res) => {
    const text = req.body?.text;

    if (typeof text !== 'string' || !text.trim()) {
        return res.status(400).json({ ok: false, message: '"text" é obrigatório.' });
    }

    try {
        const vector = await embed(text.slice(0, config.maxTextLength));
        res.json({ ok: true, vector, model: MODEL_ID });
    } catch (error) {
        console.error('Falha ao gerar embedding:', error);
        res.status(500).json({ ok: false, message: 'Falha ao gerar o vector.' });
    }
});

app.post('/analyze-cv', requireApiKey, upload.single('cv'), async (req, res) => {
    if (!req.file) {
        return res.status(400).json({ ok: false, message: 'Envie o ficheiro do CV no campo "cv".' });
    }

    if (req.file.mimetype !== 'application/pdf') {
        return res.status(400).json({ ok: false, message: 'Só PDFs são suportados neste endpoint.' });
    }

    try {
        const { text, usedOcr, numPages } = await extractCvText(req.file.buffer);

        if (!text) {
            return res.status(422).json({ ok: false, message: 'Não foi possível extrair texto deste CV.' });
        }

        const vector = await embed(text);

        res.json({ ok: true, text, vector, model: MODEL_ID, usedOcr, numPages });
    } catch (error) {
        console.error('Falha ao analisar CV:', error);
        res.status(500).json({ ok: false, message: 'Falha ao processar o CV.' });
    }
});

app.use((error, req, res, next) => {
    if (error instanceof multer.MulterError) {
        return res.status(413).json({ ok: false, message: 'Ficheiro demasiado grande.' });
    }
    console.error('Erro não tratado:', error);
    res.status(500).json({ ok: false, message: 'Erro interno.' });
});

app.listen(config.port, () => {
    console.log(`analisecv-service a correr na porta ${config.port}`);
    warmUpEmbedder()
        .then(() => console.log('Modelo carregado e pronto.'))
        .catch((error) => console.error('Falha ao carregar o modelo no arranque:', error));
});

import { config } from './config.js';

export function requireApiKey(req, res, next) {
    if (!config.apiKey) {
        // No API key configured — service is deliberately open (e.g. local dev).
        return next();
    }

    const provided = req.get('x-api-key');

    if (provided !== config.apiKey) {
        return res.status(401).json({ ok: false, message: 'Chave de API inválida ou em falta.' });
    }

    return next();
}

import { pipeline, env } from '@huggingface/transformers';
import { config } from './config.js';

env.cacheDir = config.modelCacheDir;

// Model weights are downloaded once into modelCacheDir on first use and reused
// on every request after that (and across restarts) — this is the whole point
// of running the model here instead of in each recruiter's browser.
let embedderPromise = null;

function getEmbedder() {
    if (!embedderPromise) {
        embedderPromise = pipeline('feature-extraction', config.modelId, { dtype: 'q8' }).catch((error) => {
            // Don't cache a failed load (e.g. transient network issue) — let the
            // next request try again instead of failing forever until restart.
            embedderPromise = null;
            throw error;
        });
    }
    return embedderPromise;
}

export async function embed(text) {
    const embedder = await getEmbedder();
    const output = await embedder(text, { pooling: 'mean', normalize: true });
    return Array.from(output.data);
}

export async function warmUpEmbedder() {
    await getEmbedder();
}

export const MODEL_ID = config.modelId;

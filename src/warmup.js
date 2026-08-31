// Run once after deployment (npm run warmup) to download and cache the model
// before real traffic arrives, so the first CV analysis request isn't slow.
import { warmUpEmbedder, MODEL_ID } from './embedding.js';

console.log(`A descarregar/carregar o modelo ${MODEL_ID}...`);

warmUpEmbedder()
    .then(() => {
        console.log('Modelo pronto e em cache.');
        process.exit(0);
    })
    .catch((error) => {
        console.error('Falha ao carregar o modelo:', error);
        process.exit(1);
    });

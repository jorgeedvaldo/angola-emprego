# analisecv-service

Serviço Node.js **independente** que faz a análise de CVs para o Angola Emprego: extrai o texto de um PDF (com OCR automático para CVs digitalizados/imagem) e gera o vector de compatibilidade (embedding) com o modelo `paraphrase-multilingual-MiniLM-L12-v2` (multilingue, cobre português).

Este repositório/branch contém **só este serviço** — não tem nada do site Laravel. A ideia é correr isto num subdomínio próprio (ex: `analisecv.angolaemprego.com`), separado do hosting cPanel do site principal, porque o modelo de IA (≈120MB) só pode ser descarregado **uma vez para este servidor** — não para o browser de cada recrutador.

> **Estado actual:** este serviço está pronto e testado isoladamente, mas o Laravel (branch `cursor/empresa-paginas-vagas-b9ed`) ainda não foi ligado a ele — continua a fazer tudo no browser. Ligar os dois lados (o Laravel passar a chamar este serviço em vez de correr o modelo no browser) é o próximo passo, depois deste serviço estar no ar.

## Requisitos de hospedagem

- **Precisa de um host com Node.js persistente** (processo Node a correr, não PHP/cPanel partilhado). Ex: um VPS pequeno, Railway, Render, Fly.io, DigitalOcean App Platform, etc.
- Node.js 18 ou superior.
- ~1-2GB de RAM livre (o modelo fica todo em memória depois de carregado).
- ~500MB de espaço em disco (dependências + modelo em cache).

## Instalação

```bash
npm install
cp .env.example .env
```

Edite o `.env`:
- `API_KEY` — gere uma chave secreta (`openssl rand -hex 32`) e guarde-a também no lado do Laravel. **Nunca deixe vazio em produção.**
- `ALLOWED_ORIGINS` — normalmente `https://angolaemprego.com` (só é relevante se o browser chamar este serviço directamente; se for sempre o Laravel a chamar servidor-a-servidor, pode deixar em branco).

## Pré-carregar o modelo (recomendado antes de ligar o tráfego)

```bash
npm run warmup
```

Isto descarrega o modelo (uma única vez, fica em `MODEL_CACHE_DIR`) e confirma que carrega correctamente, sem obrigar o primeiro pedido real a esperar por isso.

## Arrancar o serviço

```bash
npm start
```

Por defeito escuta na porta `3000` (variável `PORT`). **Use um gestor de processos** (PM2, systemd, ou o que o seu host oferecer) para reiniciar o serviço automaticamente se o servidor reiniciar — um `node src/server.js` sozinho não sobrevive a um reboot.

Exemplo com PM2:
```bash
npm install -g pm2
pm2 start src/server.js --name analisecv
pm2 save
pm2 startup
```

### Expor em HTTPS num subdomínio

Este serviço não faz HTTPS sozinho — precisa de um proxy reverso (nginx, Caddy, ou o proxy do seu host) a apontar `analisecv.angolaemprego.com` para `http://127.0.0.1:3000`. Exemplo mínimo em nginx:

```nginx
server {
    server_name analisecv.angolaemprego.com;
    location / {
        proxy_pass http://127.0.0.1:3000;
        proxy_set_header Host $host;
        client_max_body_size 12m;
    }
}
```

(o `client_max_body_size` tem de ser maior que `MAX_UPLOAD_BYTES` do `.env`, para o nginx não rejeitar o upload do CV antes de chegar ao Node.)

## API

Todos os pedidos (excepto `/health`) precisam do cabeçalho `X-API-Key: <a sua API_KEY>`.

### `GET /health`
Verificação simples de que o serviço está no ar.
```json
{ "ok": true, "model": "Xenova/paraphrase-multilingual-MiniLM-L12-v2" }
```

### `POST /embed`
Gera o vector de um texto (usado para a descrição da vaga).

Body (`application/json`):
```json
{ "text": "Descrição da vaga em texto simples..." }
```

Resposta:
```json
{ "ok": true, "vector": [0.01, -0.02, ...], "model": "Xenova/paraphrase-multilingual-MiniLM-L12-v2" }
```
`vector` tem sempre 384 números.

### `POST /analyze-cv`
Recebe o PDF do CV, extrai o texto (com OCR automático se for uma imagem digitalizada) e devolve o texto + o vector.

Body: `multipart/form-data`, campo `cv` com o ficheiro PDF.

Resposta:
```json
{
  "ok": true,
  "text": "texto extraído do CV...",
  "vector": [0.01, -0.02, ...],
  "model": "Xenova/paraphrase-multilingual-MiniLM-L12-v2",
  "usedOcr": false,
  "numPages": 2
}
```

Erros comuns: `400` (falta o ficheiro, ou não é PDF), `413` (ficheiro maior que `MAX_UPLOAD_BYTES`), `422` (não conseguiu extrair nenhum texto), `401` (chave de API errada/em falta).

## Testado

- Extracção de texto de PDF com camada de texto (`pdfjs-dist`).
- Fallback automático para OCR (`tesseract.js`) quando o PDF não tem texto (CV digitalizado como imagem).
- Autenticação por API key e validação de pedidos.
- O serviço não cai por completo se o Tesseract falhar a meio (ex: problema de rede momentâneo) — fica a servir os outros pedidos.

**Não foi possível testar neste ambiente** (sandbox sem acesso à Hugging Face nem ao CDN de dados do Tesseract): o download real do modelo/idioma e a exactidão do OCR em CVs reais. Recomenda-se correr `npm run warmup` logo após o primeiro deploy e testar `/analyze-cv` com um CV real antes de ligar isto ao site principal.

## Segurança / dependências conhecidas

`npm audit` reporta 4 avisos "high" sem correcção disponível ainda, ambos em dependências transitivas:
- `adm-zip` (usado só durante a instalação do `onnxruntime-node`, não em pedidos de utilizadores).
- `sharp`/`libvips` (incluído pelo `@huggingface/transformers` para processamento de imagem; o pipeline de texto usado aqui — `feature-extraction` — não o invoca).

Corra `npm audit` periodicamente depois do deploy; se saírem correcções, actualize as dependências.

## Variáveis de ambiente

Ver `.env.example` para a lista completa e comentada.

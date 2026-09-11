#!/usr/bin/env bash
#
# Corre no servidor de alojamento, enviado pelo SSH a partir do GitHub Actions.
# Também pode ser corrido à mão no Terminal do cPanel:
#
#   cd ~/angolaemprego.com && bash deploy/publicar.sh
#
# Variáveis aceites:
#   APP_DIR        pasta da aplicação no servidor (por omissão, a pasta actual)
#   PHP_BIN        executável do PHP (em cPanel costuma ser ea-php81, ea-php82…)
#   DEPLOY_BRANCH  ramo a publicar (por omissão main)
#   DEPLOY_COMMIT  commit que se espera ficar activo, para conferir no fim
#   FORCE_RESET=1  deita fora alterações feitas à mão no servidor (ver abaixo)

set -euo pipefail

APP_DIR="${APP_DIR:-$(pwd)}"
PHP_BIN="${PHP_BIN:-php}"
DEPLOY_BRANCH="${DEPLOY_BRANCH:-main}"
DEPLOY_COMMIT="${DEPLOY_COMMIT:-}"
FORCE_RESET="${FORCE_RESET:-0}"

passo() { printf '\n\033[1m==> %s\033[0m\n' "$1"; }
aviso() { printf '    [aviso] %s\n' "$1"; }

passo "Pasta da aplicação"
cd "$APP_DIR"
echo "    $(pwd)"

if [ ! -d .git ]; then
    echo "    ERRO: $APP_DIR não é um repositório git." >&2
    echo "    Clone o repositório aqui, ou use o Git Version Control do cPanel." >&2
    exit 1
fi

if [ ! -f artisan ]; then
    aviso "não encontrei o ficheiro artisan — confirme que APP_DIR aponta para a raiz do Laravel."
fi

passo "Estado actual"
echo "    commit: $(git rev-parse --short HEAD) ($(git rev-parse --abbrev-ref HEAD))"

# Guardado antes do pull para, mais abaixo, saber se as dependências mudaram.
LOCK_ANTES="$(git rev-parse HEAD:composer.lock 2>/dev/null || echo nenhum)"

passo "A buscar o código novo"
git fetch --prune origin "$DEPLOY_BRANCH"

if [ "$FORCE_RESET" = "1" ]; then
    # Descarta o que esteja alterado no servidor. Só quando se sabe que não há
    # nada de valor lá — edições feitas directamente no cPanel perdem-se.
    aviso "FORCE_RESET activo: alterações locais no servidor serão descartadas."
    git checkout -B "$DEPLOY_BRANCH" "origin/$DEPLOY_BRANCH"
    git reset --hard "origin/$DEPLOY_BRANCH"
else
    # Sem forçar: se alguém mexeu nos ficheiros do servidor, a publicação pára
    # com erro em vez de apagar esse trabalho sem avisar.
    if [ -n "$(git status --porcelain)" ]; then
        echo "    ERRO: há alterações por commitar no servidor:" >&2
        git status --short >&2
        echo "    Resolva-as, ou repita com FORCE_RESET=1 para as descartar." >&2
        exit 1
    fi

    git checkout "$DEPLOY_BRANCH" 2>/dev/null || git checkout -B "$DEPLOY_BRANCH" "origin/$DEPLOY_BRANCH"
    git merge --ff-only "origin/$DEPLOY_BRANCH"
fi

echo "    agora em: $(git rev-parse --short HEAD)"

passo "Dependências"
if [ ! -d vendor ]; then
    aviso "a pasta vendor não existe — a aplicação não arranca sem ela."
fi

LOCK_DEPOIS="$(git rev-parse HEAD:composer.lock 2>/dev/null || echo nenhum)"

if [ "$LOCK_ANTES" = "$LOCK_DEPOIS" ]; then
    # O caso normal. Correr o composer em todas as publicações só traria o risco
    # de uma falha dele (versão de PHP diferente, memória, rede) deitar abaixo
    # uma publicação que nem sequer mexeu em dependências.
    echo "    composer.lock não mudou — nada a instalar."
else
    echo "    composer.lock mudou nesta publicação."

    if command -v composer >/dev/null 2>&1; then
        COMPOSER_CMD="composer"
    elif [ -f composer.phar ]; then
        COMPOSER_CMD="$PHP_BIN composer.phar"
    else
        COMPOSER_CMD=""
    fi

    if [ -n "$COMPOSER_CMD" ]; then
        $COMPOSER_CMD install --no-dev --optimize-autoloader --no-interaction --prefer-dist
    else
        # Alojamento partilhado muitas vezes não tem composer, e aqui isso é
        # grave: o código novo já está no servidor e as dependências dele não.
        echo "    ERRO: as dependências mudaram e não há composer no servidor." >&2
        echo "    Actualize vendor/ à mão (envie o vendor.zip) e repita a publicação." >&2
        exit 1
    fi
fi

passo "Base de dados"
"$PHP_BIN" artisan migrate --force

passo "Limpeza de cache"
# Nota: não se faz route:cache. routes/web.php tem rotas definidas com closures
# e o Laravel não as consegue serializar — o comando falharia e deixaria o site
# em baixo.
"$PHP_BIN" artisan optimize:clear

if [ ! -L public/storage ] && [ ! -d public/storage ]; then
    aviso "public/storage não existe; a criar a ligação simbólica."
    "$PHP_BIN" artisan storage:link || aviso "storage:link falhou — crie a ligação à mão."
fi

passo "Confirmação"
ACTUAL="$(git rev-parse HEAD)"
echo "    commit activo: $ACTUAL"

if [ -n "$DEPLOY_COMMIT" ] && [ "$ACTUAL" != "$DEPLOY_COMMIT" ]; then
    # Acontece quando entrou outro commit na main entretanto. Não é
    # necessariamente mau, mas quem publicou tem de saber que o que está no ar
    # não é exactamente o que mandou publicar.
    echo "    ERRO: esperava $DEPLOY_COMMIT mas ficou $ACTUAL." >&2
    exit 1
fi

printf '\n\033[1mPublicado com sucesso.\033[0m\n'

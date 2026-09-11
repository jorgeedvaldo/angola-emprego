#!/usr/bin/env bash
#
# Corre no servidor de alojamento, enviado pelo SSH a partir do GitHub Actions.
#
# Faz o mesmo que se faz à mão no Terminal do cPanel:
#
#   cd ~/angolaemprego.com
#   git pull
#
# Nada mais. Migrações, composer e limpeza de cache continuam a ser decisão sua
# — o script só avisa quando este commit traz alguma coisa que as exija. Para as
# fazer correr aqui, veja as variáveis RUN_* no fim deste comentário.
#
# Variáveis aceites:
#   APP_DIR        pasta da aplicação no servidor (por omissão, a pasta actual)
#   PHP_BIN        executável do PHP (em cPanel, ex.: /usr/local/bin/ea-php81)
#   DEPLOY_COMMIT  commit que se espera ficar activo, só para conferir no fim
#   RUN_MIGRATIONS=1   corre também 'artisan migrate --force'
#   RUN_COMPOSER=1     corre também 'composer install' quando o lock mudou
#   CLEAR_CACHE=1      corre também 'artisan optimize:clear'

set -euo pipefail

APP_DIR="${APP_DIR:-$(pwd)}"
PHP_BIN="${PHP_BIN:-php}"
DEPLOY_COMMIT="${DEPLOY_COMMIT:-}"
RUN_MIGRATIONS="${RUN_MIGRATIONS:-0}"
RUN_COMPOSER="${RUN_COMPOSER:-0}"
CLEAR_CACHE="${CLEAR_CACHE:-0}"

passo() { printf '\n\033[1m==> %s\033[0m\n' "$1"; }
aviso() { printf '    \033[33m[atenção]\033[0m %s\n' "$1"; }

passo "Pasta da aplicação"
cd "$APP_DIR"
echo "    $(pwd)"

if [ ! -d .git ]; then
    echo "    ERRO: $APP_DIR não é um repositório git." >&2
    echo "    Confirme o valor de DEPLOY_PATH com 'pwd' no Terminal do cPanel." >&2
    exit 1
fi

ANTES="$(git rev-parse HEAD)"
echo "    ramo: $(git rev-parse --abbrev-ref HEAD)"
echo "    commit: $(git rev-parse --short HEAD)"

passo "git pull"
git pull

DEPOIS="$(git rev-parse HEAD)"

if [ "$ANTES" = "$DEPOIS" ]; then
    echo "    (já estava actualizado)"
else
    echo "    $(git rev-parse --short "$ANTES") -> $(git rev-parse --short "$DEPOIS")"
fi

# A partir daqui nada altera o site: é só dizer o que este commit trouxe e que
# possa precisar de um passo seu, como já acontece hoje quando corre as
# migrações de propósito.
passo "O que mudou"

if [ "$ANTES" != "$DEPOIS" ]; then
    MIGRACOES="$(git diff --name-only --diff-filter=A "$ANTES" "$DEPOIS" -- database/migrations || true)"
    LOCK="$(git diff --name-only "$ANTES" "$DEPOIS" -- composer.lock || true)"
    ASSETS="$(git diff --name-only "$ANTES" "$DEPOIS" -- package.json resources/js resources/css || true)"
else
    MIGRACOES=""
    LOCK=""
    ASSETS=""
fi

if [ -n "$MIGRACOES" ]; then
    aviso "este commit traz migrações novas:"
    printf '        %s\n' $MIGRACOES
    if [ "$RUN_MIGRATIONS" != "1" ]; then
        aviso "corra-as quando lhe der jeito: $PHP_BIN artisan migrate --force"
    fi
fi

if [ -n "$LOCK" ]; then
    aviso "o composer.lock mudou — a pasta vendor/ neste servidor ficou desactualizada."
    if [ "$RUN_COMPOSER" != "1" ]; then
        aviso "actualize vendor/ (composer install, ou o envio do vendor.zip)."
    fi
fi

if [ -n "$ASSETS" ]; then
    aviso "houve alterações em assets (resources/js, resources/css ou package.json);"
    aviso "se o site usa os ficheiros compilados, é preciso gerá-los e enviá-los."
fi

if [ -z "$MIGRACOES$LOCK$ASSETS" ]; then
    echo "    nada que exija um passo adicional."
fi

# Passos extra, desligados por omissão. Ligam-se pelas variáveis RUN_*, que o
# workflow passa a partir de segredos do GitHub — assim a publicação continua a
# ser o que você faz à mão, a não ser que decida o contrário.
if [ "$RUN_COMPOSER" = "1" ] && [ -n "$LOCK" ]; then
    passo "composer install"
    if command -v composer >/dev/null 2>&1; then
        composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
    elif [ -f composer.phar ]; then
        "$PHP_BIN" composer.phar install --no-dev --optimize-autoloader --no-interaction --prefer-dist
    else
        echo "    ERRO: RUN_COMPOSER=1 mas não há composer neste servidor." >&2
        exit 1
    fi
fi

if [ "$RUN_MIGRATIONS" = "1" ]; then
    passo "artisan migrate"
    "$PHP_BIN" artisan migrate --force
fi

if [ "$CLEAR_CACHE" = "1" ]; then
    passo "artisan optimize:clear"
    # Nota: nunca route:cache. routes/web.php tem rotas definidas com closures,
    # que o Laravel não consegue serializar — o comando falha e deixa o site em
    # baixo.
    "$PHP_BIN" artisan optimize:clear
fi

passo "Resultado"
echo "    commit activo: $(git rev-parse HEAD)"

if [ -n "$DEPLOY_COMMIT" ] && [ "$DEPOIS" != "$DEPLOY_COMMIT" ]; then
    # Não é erro: pode ter entrado outro commit na main entretanto, ou o git ter
    # feito um merge em vez de avançar a direito. Mas quem publicou tem de saber
    # que o que está no ar não é exactamente o que mandou publicar.
    aviso "esperava $DEPLOY_COMMIT"
    aviso "o servidor ficou noutro commit — veja o 'git log' antes de assumir que está actualizado."
fi

printf '\n\033[1mActualizado.\033[0m\n'

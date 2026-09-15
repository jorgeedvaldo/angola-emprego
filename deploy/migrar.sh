#!/usr/bin/env bash
#
# Corre as migrações no servidor de alojamento, enviado pelo SSH a partir do
# GitHub Actions. É o que se faria à mão no Terminal do cPanel:
#
#   cd ~/angolaemprego.com
#   php artisan migrate --force
#
# A diferença é que aqui fica registo do que correu, e que antes de mexer na
# base de dados se pode ver o que está por aplicar — e guardar uma cópia.
#
# Ao contrário do publicar.sh, este script NÃO faz git pull: corre as migrações
# que já estão no servidor. A ordem é sempre publicar primeiro, migrar depois.
#
# Variáveis aceites:
#   APP_DIR        pasta da aplicação no servidor
#   PHP_BIN        executável do PHP (em cPanel, ex.: /usr/local/bin/ea-php81)
#   ACCAO          estado | simular | migrar          (por omissão: estado)
#   COPIA_BD       1 para guardar uma cópia da base de dados antes de migrar
#   LIMPAR_CACHE   1 para correr 'artisan optimize:clear' no fim

set -euo pipefail

APP_DIR="${APP_DIR:-$(pwd)}"
PHP_BIN="${PHP_BIN:-php}"
ACCAO="${ACCAO:-estado}"
COPIA_BD="${COPIA_BD:-0}"
LIMPAR_CACHE="${LIMPAR_CACHE:-0}"

passo() { printf '\n\033[1m==> %s\033[0m\n' "$1"; }
aviso() { printf '    \033[33m[atenção]\033[0m %s\n' "$1"; }
erro()  { printf '    \033[31m[erro]\033[0m %s\n' "$1" >&2; }

# O migrate:status sai com erro quando ainda não há tabela de migrações — o caso
# de uma base de dados vazia. Não é motivo para parar o script: é precisamente aí
# que se quer migrar.
estado_das_migracoes() {
    if ! "$PHP_BIN" artisan migrate:status; then
        aviso "não foi possível ler o estado das migrações (base de dados vazia, ou sem ligação)."
    fi
}

case "$ACCAO" in
    estado|simular|migrar) ;;
    *) erro "Acção desconhecida: '$ACCAO'. Use estado, simular ou migrar."; exit 1 ;;
esac

passo "Pasta da aplicação"
cd "$APP_DIR"
echo "    $(pwd)"

if [ ! -f artisan ]; then
    erro "$APP_DIR não parece ser a aplicação: não tem o ficheiro artisan."
    erro "Confirme o valor de DEPLOY_PATH com 'pwd' no Terminal do cPanel."
    exit 1
fi

if [ -d .git ]; then
    echo "    commit: $(git rev-parse --short HEAD) ($(git rev-parse --abbrev-ref HEAD))"
fi

if ! "$PHP_BIN" -v >/dev/null 2>&1; then
    erro "O PHP não corre com '$PHP_BIN'."
    erro "Em cPanel o caminho costuma ser /usr/local/bin/ea-php81 — veja o segredo DEPLOY_PHP."
    exit 1
fi

echo "    php: $("$PHP_BIN" -r 'echo PHP_VERSION;')"

# Mostra-se sempre o que está por aplicar, seja qual for a acção: quem só quis
# ver fica com a resposta, e quem vai migrar vê antes o que vai acontecer.
passo "Migrações por aplicar"
estado_das_migracoes

if [ "$ACCAO" = "estado" ]; then
    printf '\n\033[1mNada foi alterado.\033[0m\n'
    exit 0
fi

if [ "$ACCAO" = "simular" ]; then
    # --pretend imprime o SQL em vez de o correr. Serve para ver ao certo o que
    # vai mexer na base de dados antes de deixar mexer.
    #
    # O --force vai junto só para calar a pergunta "está em produção, tem a
    # certeza?", que aqui não teria quem a respondesse. Com --pretend nada é
    # escrito, seja com --force ou sem ele.
    passo "SQL que as migrações iriam correr (sem correr nada)"
    "$PHP_BIN" artisan migrate --pretend --force

    printf '\n\033[1mNada foi alterado.\033[0m\n'
    exit 0
fi

# ---------------------------------------------------------------------------
# Cópia da base de dados
#
# Uma migração que corre mal não se desfaz sozinha, e o `migrate:rollback` só
# desfaz o que a migração souber desfazer. A cópia é o que permite voltar atrás
# de verdade. Se foi pedida e não se conseguiu fazer, pára-se aqui: mais vale
# não migrar do que migrar sem rede.
# ---------------------------------------------------------------------------
if [ "$COPIA_BD" = "1" ]; then
    passo "Cópia da base de dados"

    if ! command -v mysqldump >/dev/null 2>&1; then
        erro "Não há mysqldump neste servidor, e foi pedida uma cópia da base de dados."
        erro "Faça a cópia pelo phpMyAdmin do cPanel, ou volte a correr sem a cópia."
        exit 1
    fi

    # As credenciais vêm da configuração do Laravel, e não de um sed ao .env:
    # assim usa-se exactamente a mesma ligação que a aplicação usa.
    CREDENCIAIS="$("$PHP_BIN" -r '
        require "vendor/autoload.php";
        $app = require "bootstrap/app.php";
        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        $c = config("database.connections." . config("database.default"));
        echo implode("\n", [$c["driver"] ?? "", $c["host"] ?? "", $c["port"] ?? "3306", $c["database"] ?? "", $c["username"] ?? "", $c["password"] ?? ""]);
    ')"

    BD_DRIVER="$(echo "$CREDENCIAIS" | sed -n 1p)"
    BD_HOST="$(echo "$CREDENCIAIS" | sed -n 2p)"
    BD_PORTA="$(echo "$CREDENCIAIS" | sed -n 3p)"
    BD_NOME="$(echo "$CREDENCIAIS" | sed -n 4p)"
    BD_UTILIZADOR="$(echo "$CREDENCIAIS" | sed -n 5p)"
    BD_SENHA="$(echo "$CREDENCIAIS" | sed -n 6p)"

    if [ -z "$BD_NOME" ]; then
        erro "Não foi possível ler o nome da base de dados a partir da configuração."
        exit 1
    fi

    if [ "$BD_DRIVER" != "mysql" ]; then
        erro "A cópia automática só sabe copiar MySQL, e esta ligação é '$BD_DRIVER'."
        erro "Faça a cópia à mão, ou volte a correr sem a cópia."
        exit 1
    fi

    mkdir -p storage/backups

    # A senha num ficheiro com permissões 600, e não na linha de comando: o que
    # está na linha de comando aparece a quem correr um 'ps' no servidor.
    CONF_TEMP="$(mktemp)"
    chmod 600 "$CONF_TEMP"
    trap 'rm -f "$CONF_TEMP"' EXIT

    {
        echo "[client]"
        echo "host=$BD_HOST"
        echo "port=$BD_PORTA"
        echo "user=$BD_UTILIZADOR"
        echo "password=$BD_SENHA"
    } > "$CONF_TEMP"

    FICHEIRO="storage/backups/bd-$(date +%Y%m%d-%H%M%S).sql.gz"

    if mysqldump --defaults-extra-file="$CONF_TEMP" \
                 --single-transaction --quick --routines --no-tablespaces \
                 "$BD_NOME" | gzip > "$FICHEIRO"; then
        echo "    guardada em $FICHEIRO ($(du -h "$FICHEIRO" | cut -f1))"
        echo "    para repor: gunzip -c $FICHEIRO | mysql -u UTILIZADOR -p $BD_NOME"
    else
        erro "A cópia da base de dados falhou. Não se migrou nada."
        rm -f "$FICHEIRO"
        exit 1
    fi

    rm -f "$CONF_TEMP"
    trap - EXIT
else
    aviso "sem cópia da base de dados — uma migração que corra mal não se desfaz sozinha."
fi

passo "artisan migrate"
"$PHP_BIN" artisan migrate --force

passo "Como ficou"
estado_das_migracoes

if [ "$LIMPAR_CACHE" = "1" ]; then
    passo "artisan optimize:clear"
    # Nunca route:cache. O routes/web.php tem rotas definidas com closures, que o
    # Laravel não consegue serializar — o comando falha e deixa o site em baixo.
    "$PHP_BIN" artisan optimize:clear
fi

printf '\n\033[1mMigrações aplicadas.\033[0m\n'

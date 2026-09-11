#!/usr/bin/env bash
#
# Corra isto no Terminal do cPanel para obter os valores dos segredos do GitHub:
#
#   bash ~/angolaemprego.com/deploy/mostrar-dados.sh
#
# Não mostra a chave privada — essa obtém-se à parte (ver deploy/README.md).

titulo() { printf '\n\033[1m%s\033[0m\n' "$1"; }
valor()  { printf '  \033[32m%s\033[0m\n' "$1"; }
nota()   { printf '  %s\n' "$1"; }

titulo "DEPLOY_USER"
valor "$(whoami)"

titulo "DEPLOY_HOST"
HOST="$(hostname -f 2>/dev/null || hostname)"
valor "$HOST"
nota "(se o seu alojamento lhe deu outro endereço para SSH, use esse)"

titulo "DEPLOY_PATH"
APP=""
while IFS= read -r encontrado; do
    APP="$(dirname "$encontrado")"
    valor "$APP"
done < <(find "$HOME" -maxdepth 3 -name artisan -not -path '*/vendor/*' 2>/dev/null)

if [ -z "$APP" ]; then
    nota "Não encontrei nenhum ficheiro 'artisan' até três níveis abaixo de $HOME."
    nota "Vá à pasta da aplicação e corra 'pwd' para obter o caminho."
else
    if [ -d "$APP/.git" ]; then
        nota "Confirmado: é um repositório git."
        nota "Remote: $(git -C "$APP" remote get-url origin 2>/dev/null || echo 'nenhum!')"
        nota "Ramo actual: $(git -C "$APP" rev-parse --abbrev-ref HEAD 2>/dev/null || echo '?')"
    else
        nota "ATENÇÃO: esta pasta não é um repositório git — o git pull não vai funcionar."
    fi
fi

titulo "DEPLOY_PORT"
PORTA="$(grep -hoP '^\s*Port\s+\K[0-9]+' /etc/ssh/sshd_config 2>/dev/null | head -1)"
if [ -n "$PORTA" ]; then
    valor "$PORTA"
    [ "$PORTA" = "22" ] && nota "É a porta por omissão: não precisa de criar este segredo."
else
    nota "Não consegui ler a configuração do SSH (normal em alojamento partilhado)."
    nota "Veja a porta na página 'SSH Access' do cPanel. Se for 22, não crie o segredo."
fi

titulo "DEPLOY_KNOWN_HOSTS"
if command -v ssh-keyscan >/dev/null 2>&1; then
    SAIDA="$(ssh-keyscan -p "${PORTA:-22}" "$HOST" 2>/dev/null)"
    if [ -n "$SAIDA" ]; then
        printf '\033[32m%s\033[0m\n' "$SAIDA"
        nota ""
        nota "Copie as linhas todas acima (são várias)."
    else
        nota "O ssh-keyscan não devolveu nada a partir daqui."
        nota "Corra-o no SEU computador: ssh-keyscan -p ${PORTA:-22} $HOST"
    fi
else
    nota "Sem ssh-keyscan neste servidor."
    nota "Corra no SEU computador: ssh-keyscan -p ${PORTA:-22} $HOST"
fi

titulo "DEPLOY_PHP (opcional)"
nota "Só é usado se ligar as migrações ou a limpeza de cache. Candidatos:"
for php in php /usr/local/bin/ea-php8*[0-9] /opt/cpanel/ea-php8*/root/usr/bin/php; do
    caminho="$(command -v "$php" 2>/dev/null || true)"
    [ -z "$caminho" ] && [ -x "$php" ] && caminho="$php"
    [ -z "$caminho" ] && continue
    versao="$("$caminho" -r 'echo PHP_VERSION;' 2>/dev/null || echo '?')"
    valor "$caminho  (PHP $versao)"
done
nota "Escolha um com PHP 8.0 ou superior, que é o que este projecto exige."

titulo "A chave está autorizada?"
if [ -f "$HOME/.ssh/authorized_keys" ]; then
    nota "authorized_keys tem $(grep -c . "$HOME/.ssh/authorized_keys") chave(s) autorizada(s)."
    nota "Comentários das chaves:"
    awk '{print "    - " $NF}' "$HOME/.ssh/authorized_keys" 2>/dev/null | head -5
else
    nota "NÃO existe ~/.ssh/authorized_keys — nenhuma chave está autorizada ainda."
    nota "cPanel -> SSH Access -> Manage SSH Keys -> Import, e depois Authorize."
fi

printf '\n\033[1mFalta só a DEPLOY_SSH_KEY (a chave privada).\033[0m\n'
nota "Veja a secção 'A chave privada' no deploy/README.md."

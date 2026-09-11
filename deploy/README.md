# Publicação automática na hospedagem

Sempre que a `main` muda, o GitHub faz por si o que faria à mão:

```bash
cd ~/angolaemprego.com
git pull
```

Nada mais. As migrações, o composer e a limpeza de cache continuam a ser decisão
sua — o script apenas **avisa** quando o commit publicado traz alguma coisa que
os exija, para não ter de ir verificar de cada vez.

O que corre no servidor é o `deploy/publicar.sh`, enviado pelo próprio canal do
SSH: corre sempre a versão que está no commit publicado, nunca uma cópia
esquecida no servidor.

## O que é preciso configurar uma vez

### 1. Autorizar a chave no cPanel

A chave **pública** tem de estar autorizada no alojamento:

**cPanel → SSH Access → Manage SSH Keys → Import Key** (cole a chave pública),
e depois **Manage → Authorize**.

Para confirmar que ficou a funcionar, a partir do seu computador:

```bash
ssh -i ~/.ssh/a_sua_chave_privada yqqnyhrgnt@SERVIDOR "pwd && git --version"
```

Se isto não entrar, o workflow também não entra — resolva aqui primeiro.

### 2. Descobrir os valores a guardar

No **Terminal do cPanel**:

```bash
cd ~/angolaemprego.com && pwd          # -> valor de DEPLOY_PATH
git remote -v                          # confirma que é mesmo um repositório git
whoami                                 # -> valor de DEPLOY_USER
ls /usr/local/bin/ea-php*              # -> valor de DEPLOY_PHP (ex.: ea-php81)
```

O `DEPLOY_PATH` tem de ser a pasta onde está o ficheiro `artisan`.

### 3. Guardar a impressão digital do servidor

No **seu computador** (não no cPanel):

```bash
ssh-keyscan -p 22 SERVIDOR
```

Copie as linhas todas — são o valor de `DEPLOY_KNOWN_HOSTS`. Sem isto o GitHub
não tem como distinguir o seu servidor de outro que responda no mesmo endereço,
e por isso o workflow recusa-se a correr sem esse segredo.

### 4. Criar os segredos no GitHub

**Settings → Secrets and variables → Actions → New repository secret**:

| Segredo | Valor | Obrigatório |
|---|---|---|
| `DEPLOY_SSH_KEY` | a chave **privada** inteira, incluindo as linhas `-----BEGIN…` e `-----END…` | sim |
| `DEPLOY_KNOWN_HOSTS` | o resultado do `ssh-keyscan` do ponto 3 | sim |
| `DEPLOY_HOST` | endereço do servidor (ex.: `server40.xxxx.com`) | sim |
| `DEPLOY_USER` | `yqqnyhrgnt` | sim |
| `DEPLOY_PATH` | `/home/yqqnyhrgnt/angolaemprego.com` | sim |
| `DEPLOY_PORT` | porta SSH, se não for a 22 | não |
| `DEPLOY_PHP` | executável do PHP, ex.: `/usr/local/bin/ea-php81` | não |

A chave privada tem de ser colada **tal e qual**, com a última linha em branco
incluída. Se estiver no formato novo (`OPENSSH PRIVATE KEY`) funciona na mesma.

### 5. Deixar o servidor puxar do GitHub

O servidor precisa de conseguir ele próprio ir buscar o código. No Terminal do
cPanel:

```bash
cd ~/angolaemprego.com && git fetch origin main
```

Se pedir palavra-passe ou der erro de permissões, o repositório é privado e é
preciso uma chave de leitura:

```bash
ssh-keygen -t ed25519 -C "angolaemprego-deploy" -f ~/.ssh/github_deploy -N ""
cat ~/.ssh/github_deploy.pub
```

Cole essa chave em **GitHub → Settings → Deploy keys → Add deploy key**
(sem marcar "Allow write access") e mude o remote para SSH:

```bash
cd ~/angolaemprego.com
git remote set-url origin git@github.com:jorgeedvaldo/angola-emprego.git
printf 'Host github.com\n  IdentityFile ~/.ssh/github_deploy\n' >> ~/.ssh/config
git fetch origin main   # deve funcionar sem pedir nada
```

## Primeira publicação

Não espere por um commit. Vá a **Actions → Publicar na hospedagem → Run
workflow** e corra à mão. Se falhar, o registo diz exactamente em que passo.

## Os avisos

Depois do `git pull`, o script compara o que havia antes com o que veio agora e
diz-lhe se este commit precisa de mais alguma coisa da sua parte:

```
==> O que mudou
    [atenção] este commit traz migrações novas:
        database/migrations/2026_09_11_000001_criar_tabela.php
    [atenção] corra-as quando lhe der jeito: php artisan migrate --force
    [atenção] o composer.lock mudou — a pasta vendor/ ficou desactualizada.
```

Quando não há nada a fazer, diz `nada que exija um passo adicional`. É só
informação: o script não altera nada além do `git pull`.

## Ligar passos adicionais (opcional)

Se quiser que a publicação passe a fazer mais do que o `git pull`, crie o
segredo correspondente com o valor `1`:

| Segredo | O que passa a correr |
|---|---|
| `DEPLOY_RUN_MIGRATIONS` | `artisan migrate --force` em cada publicação |
| `DEPLOY_RUN_COMPOSER` | `composer install` quando o `composer.lock` mudou |
| `DEPLOY_CLEAR_CACHE` | `artisan optimize:clear` |

Para apagar, basta remover o segredo. Recomendo começar sem nenhum, tal como
faz hoje, e ligar as migrações só quando confiar no resto.

Uma nota técnica: **nunca se corre `route:cache`** neste projecto. O
`routes/web.php` tem rotas definidas com closures, que o Laravel não consegue
serializar — o comando falha e deixa o site em baixo.

## Correr à mão, sem o GitHub

```bash
cd ~/angolaemprego.com && bash deploy/publicar.sh
```

## Se correr mal

| Sintoma no registo | Causa |
|---|---|
| `Permission denied (publickey)` | a chave pública não está autorizada no cPanel (ponto 1), ou `DEPLOY_SSH_KEY` foi colada incompleta |
| `Host key verification failed` | `DEPLOY_KNOWN_HOSTS` errado ou de outro servidor |
| `não é um repositório git` | `DEPLOY_PATH` aponta para a pasta errada |
| `Your local changes would be overwritten` | alguém editou ficheiros directamente no cPanel; é o mesmo erro que teria no terminal. Veja-os com `git status` e decida antes de continuar |
| pede palavra-passe no `git pull` | falta a chave de leitura do ponto 5 |
| `o servidor ficou noutro commit` | entrou outra coisa na `main` entretanto, ou o `git pull` fez um merge. O site está actualizado, mas não exactamente no commit publicado |

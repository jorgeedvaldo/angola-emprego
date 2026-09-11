# Publicação automática na hospedagem

Sempre que a `main` muda, o GitHub liga-se por SSH ao alojamento e actualiza o
site. O que corre lá é o `deploy/publicar.sh`, enviado pelo próprio SSH — corre
sempre a versão que está no commit publicado, nunca uma cópia esquecida no
servidor.

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

## O que o script faz, e o que não faz

Por esta ordem: vai buscar o código, instala dependências **só se o
`composer.lock` tiver mudado**, corre as migrações, limpa a cache e confirma que
ficou no commit certo.

Três decisões que vale a pena conhecer:

- **Não corre `route:cache`.** O `routes/web.php` tem rotas definidas com
  closures, que o Laravel não consegue serializar: o comando falharia e deixava
  o site em baixo.
- **Não corre o composer em todas as publicações.** Numa hospedagem partilhada o
  composer falha por pouca memória ou por versão de PHP diferente; não faz
  sentido arriscar isso quando as dependências nem mudaram. Quando mudam e não
  há composer no servidor, a publicação pára com erro — o código novo já lá
  está sem as dependências dele, e isso tem de dar erro visível.
- **Pára se alguém editou ficheiros directamente no servidor**, em vez de os
  apagar sem avisar. Para os descartar de propósito, corra à mão com
  `FORCE_RESET=1 bash deploy/publicar.sh`.

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
| `há alterações por commitar no servidor` | alguém editou ficheiros no cPanel; veja-os com `git status` antes de decidir |
| `as dependências mudaram e não há composer` | envie o `vendor/` actualizado e repita |
| pede palavra-passe no `git fetch` | falta a chave de leitura do ponto 5 |

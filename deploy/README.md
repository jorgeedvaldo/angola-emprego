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

### 2. Descobrir todos os valores de uma vez

No **Terminal do cPanel**:

```bash
bash ~/angolaemprego.com/deploy/mostrar-dados.sh
```

Imprime `DEPLOY_USER`, `DEPLOY_HOST`, `DEPLOY_PATH`, `DEPLOY_PORT`,
`DEPLOY_KNOWN_HOSTS` e os PHP disponíveis, e diz-lhe se já há alguma chave
autorizada. Só não mostra a chave privada — essa é o ponto 3.

Se o script não conseguir gerar o `DEPLOY_KNOWN_HOSTS` (acontece quando o
alojamento não tem `ssh-keyscan`), corra isto **no seu computador**, trocando
`SERVIDOR` pelo valor de `DEPLOY_HOST`:

```bash
ssh-keyscan -p 22 SERVIDOR
```

No Windows 10 ou 11 este comando existe no PowerShell, sem instalar nada.

Copie as linhas todas — são várias. Sem esta impressão digital, o GitHub não
tem como distinguir o seu servidor de outro que responda no mesmo endereço, e é
por isso que o workflow se recusa a correr sem ela.

### 3. A chave privada

O segredo `DEPLOY_SSH_KEY` é a chave **privada** que faz par com a pública que
autorizou no ponto 1.

**Se gerou a chave no cPanel:** SSH Access → Manage SSH Keys → em *Private
Keys*, **View/Download**. O conteúdo do ficheiro é o valor do segredo.

**Se gerou a chave no seu computador:** é o ficheiro sem `.pub`, normalmente
`~/.ssh/id_rsa` (no Windows, `C:\Users\SEU_NOME\.ssh\id_rsa`).

Três coisas que costumam correr mal:

1. **Copie o ficheiro inteiro**, da linha `-----BEGIN` à linha `-----END`
   inclusive, com a mudança de linha final. Sem a última linha, o GitHub dá
   `error in libcrypto` ou `invalid format`.
2. **Não use o `.pub`.** A que vai para o GitHub é a privada; a `.pub` é a que
   vai para o cPanel.
3. **A chave não pode ter palavra-passe.** O GitHub corre sem ninguém para a
   escrever. Para confirmar, no seu computador:

   ```bash
   ssh-keygen -y -P "" -f ~/.ssh/id_rsa >/dev/null && echo "sem palavra-passe, serve"
   ```

   Se falhar, gere uma chave só para isto, sem palavra-passe:

   ```bash
   ssh-keygen -t rsa -b 4096 -N "" -C "github-deploy" -f ~/.ssh/angolaemprego_deploy
   ```

   e autorize a `.pub` nova no cPanel (ponto 1).

Antes de guardar o segredo, teste que a chave entra mesmo:

```bash
ssh -i ~/.ssh/angolaemprego_deploy yqqnyhrgnt@SERVIDOR "pwd && git --version"
```

### 4. Criar os segredos no GitHub

**Settings → Secrets and variables → Actions → New repository secret**:

| Segredo | Valor | Obrigatório |
|---|---|---|
| `DEPLOY_SSH_KEY` | a chave **privada** inteira, incluindo as linhas `-----BEGIN…` e `-----END…` | sim |
| `DEPLOY_KNOWN_HOSTS` | o resultado do `ssh-keyscan` do ponto 3 | sim |
| `DEPLOY_HOST` | endereço do servidor (ex.: `server40.xxxx.com`) | sim |
| `DEPLOY_USER` | `yqqnyhrgnt` | sim |
| `DEPLOY_PATH` | `/home/yqqnyhrgnt/angolaemprego.com` | sim |
| `DEPLOY_PORT` | porta SSH, só se não for a 22 | não |
| `DEPLOY_PHP` | executável do PHP, ex.: `/usr/local/bin/ea-php81` | não |

O `DEPLOY_PHP` **não é preciso** para a publicação normal: o script só faz `git
pull` e nunca chama o PHP. Só o crie se ligar as migrações ou a limpeza de cache
(ver mais abaixo); serve também para a mensagem de aviso mostrar o comando certo.

A chave privada é colada tal e qual, e funciona tanto no formato antigo (`RSA
PRIVATE KEY`) como no novo (`OPENSSH PRIVATE KEY`).

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

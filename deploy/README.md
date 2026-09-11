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

#### Sobre o `DEPLOY_KNOWN_HOSTS` (opcional)

Este é o único segredo que pode saltar: sem ele a publicação funciona na mesma,
aceitando o servidor que responder ao endereço.

O que se perde não é a chave. Com autenticação por chave, a **privada nunca é
enviada**: o SSH assina um desafio ligado àquela sessão, e um servidor falso não
consegue reutilizar essa assinatura para entrar no servidor verdadeiro. (Com
palavra-passe seria outra conversa — essa iria mesmo parar às mãos dele.) O que
um impostor conseguiria era receber o script de publicação — que é público,
está neste repositório — e mentir sobre o resultado.

Ou seja: sem este segredo, a publicação continua a não dar acesso a ninguém ao
seu servidor; o que deixa de haver é a garantia de que se falou mesmo com ele.
Quando o segredo falta, o workflow deixa um aviso amarelo no registo em vez de
falhar.

Para o criar, corra no **seu computador** (no Windows 10 ou 11 funciona no
PowerShell, sem instalar nada), trocando `SERVIDOR` pelo valor de `DEPLOY_HOST`:

```bash
ssh-keyscan -p 22 SERVIDOR
```

Copie as linhas todas — são várias, uma por tipo de chave. O `mostrar-dados.sh`
também o tenta gerar a partir do próprio servidor.

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
| `DEPLOY_KNOWN_HOSTS` | o resultado do `ssh-keyscan` (ver ponto 2) | não |
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

### `Connection closed by ... port 22` (saída 255)

O servidor aceitou a ligação e fechou-a a meio do aperto de mão. Repare que
**não** diz `Permission denied (publickey)` — a autenticação nem chegou ao fim,
por isso o problema raramente é a chave estar errada. Por ordem de
probabilidade:

1. **Chave RSA contra servidor antigo.** Desde o OpenSSH 8.8 o cliente já não
   assina com `ssh-rsa`/SHA-1, e muito alojamento partilhado só aceita isso. O
   workflow já acrescenta `PubkeyAcceptedKeyTypes=+ssh-rsa`, o que resolve a
   maioria dos casos. Em alternativa, gere uma chave `ed25519`, que não tem este
   problema:

   ```bash
   ssh-keygen -t ed25519 -N "" -C "github-deploy" -f ~/.ssh/angolaemprego_deploy
   ```

   e autorize a `.pub` nova no cPanel.

2. **A firewall do alojamento bloqueia o GitHub.** Os runners do GitHub mudam de
   endereço a cada corrida, e firewalls como o CSF, ou o cPHulk, fecham ligações
   de endereços desconhecidos. Peça ao suporte do alojamento para permitir SSH a
   partir do exterior, ou para libertar os
   [endereços dos runners do GitHub](https://api.github.com/meta).

3. **Acesso SSH externo desligado na conta.** O Terminal do cPanel funciona pelo
   browser e não prova que o SSH está aberto de fora. Confirme ligando-se a
   partir do seu computador:

   ```bash
   ssh -p 22 yqqnyhrgnt@SERVIDOR
   ```

   Se isto não entrar, nenhum workflow entra. É um pedido ao suporte, não uma
   correcção no código.

Quando a publicação falha, o workflow corre sozinho um passo de **Diagnóstico da
ligação** com `ssh -vvv`: as últimas linhas dizem em que ponto o servidor
desistiu, e ainda testa se a porta sequer responde.

### Outros erros

| Sintoma no registo | Causa |
|---|---|
| `Permission denied (publickey)` | a chave pública não está autorizada no cPanel (ponto 1), ou `DEPLOY_SSH_KEY` foi colada incompleta |
| `Host key verification failed` | `DEPLOY_KNOWN_HOSTS` errado, de outro servidor, ou a chave do servidor mudou. Gere-o de novo, ou apague o segredo para deixar de verificar |
| `não é um repositório git` | `DEPLOY_PATH` aponta para a pasta errada |
| `Your local changes would be overwritten` | alguém editou ficheiros directamente no cPanel; é o mesmo erro que teria no terminal. Veja-os com `git status` e decida antes de continuar |
| pede palavra-passe no `git pull` | falta a chave de leitura do ponto 5 |
| `o servidor ficou noutro commit` | entrou outra coisa na `main` entretanto, ou o `git pull` fez um merge. O site está actualizado, mas não exactamente no commit publicado |

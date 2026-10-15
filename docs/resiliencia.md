# Aguentar o MySQL a recusar ligações

```
SQLSTATE[42000] [1226] User 'yqqnyhrgnt_edv' has exceeded the
'max_user_connections' resource (current value: 30)
```

O alojamento dá **30 ligações simultâneas** a este utilizador. Quando se
esgotam, o MySQL recusa as seguintes e o site devolvia 500 em todas as páginas.

Este documento diz o que mudou, como verificar, e como desfazer.

---

## A ideia

```
Visitante → Laravel → há cópia em cache?
                        ├─ sim → serve-se, sem tocar no MySQL
                        └─ não → MySQL responde?
                                   ├─ sim → consulta, guarda duas cópias
                                   └─ não → cópia de emergência
                                              └─ não há → 503 calmo
```

Cada resultado é guardado **duas vezes**:

| | Dura | Para que serve |
|---|---|---|
| **Cópia normal** | 30 min | o dia a dia; expira para o site estar actualizado |
| **Cópia de emergência** | 7 dias | só se toca quando o MySQL não responde |

A diferença entre as duas é o ponto todo. Quando a normal expira, a de
emergência **fica** — e é ela que mantém a página de pé.

---

## O que estava a gastar ligações

A correcção com mais efeito não foi a cache. Foi esta linha, no
`AppServiceProvider`:

```php
Schema::defaultStringLength(191);   // antes
```

A fachada `Schema::` resolve o construtor de esquema, e isso **estabelece uma
ligação ao MySQL em cada requisição** — mesmo numa página sem consulta nenhuma,
mesmo num 404, mesmo num pedido a uma imagem que passe pelo Laravel. Era uma
das 30 ligações gasta por visita para pôr um número numa propriedade estática.

```php
SchemaBuilder::defaultStringLength(191);   // agora, sem fachada
```

Medido num navegador, com a cache quente, antes e depois:

| Página | Consultas | Ligação aberta (antes) | Ligação aberta (agora) |
|---|---|---|---|
| `/` | 0 | sim | **não** |
| `/vagas` | 0 | sim | **não** |
| `/noticias` | 0 | sim | **não** |
| `/sobre` | 0 | sim | **não** |

Com a cache fria são 5, 6 e 3 consultas; na segunda visita, zero.

Outras consultas que corriam em cada visita e agora estão em cache:

- o `GROUP BY` das empresas que mais contratam, na barra lateral de `/vagas` —
  varria a tabela das vagas inteira **em cada visita**, filtrada ou não;
- `Post::count()` e `Job::count()`, para a paginação;
- o `/sitemap.xml`, que varre vagas e artigos todos (6 h de cache);
- o `/feed` e os feeds de cada vaga e de cada artigo.

Foi acrescentado um índice em `jobs.company`, que é a coluna do `GROUP BY`
(migração `2026_10_15_000000_add_company_index_to_jobs_table`).

---

## Os ficheiros

| Ficheiro | O que é |
|---|---|
| `app/Services/DatabaseFallbackCache.php` | a cache com cópia de emergência |
| `app/Services/CircuitoBaseDeDados.php` | o disjuntor |
| `app/Support/FalhaDeLigacao.php` | distingue "sem ligação" de "SQL mal escrito" |
| `app/Support/Resiliente.php` | o atalho usado nos modelos e controladores |
| `app/Http/Middleware/CacheDePaginasPublicas.php` | cache do HTML já desenhado |
| `app/Exceptions/Handler.php` | devolve o 503 calmo, e só para falhas de ligação |
| `app/Console/Commands/EstadoDaResiliencia.php` | `php artisan resiliencia` |
| `public/database-unavailable.html` | a página de emergência, sem Laravel |
| `config/resiliencia.php` | os tempos e a lista de rotas |

---

## O disjuntor

Quando o MySQL recusa ligações, o pior que se pode fazer é mandar cada
visitante tentar outra vez: cada tentativa ocupa uma das 30 ligações e atrasa
a recuperação.

1. **3 falhas seguidas** de ligação → o disjuntor abre.
2. Enquanto estiver aberto, **ninguém tenta**: serve-se o que está em cache.
3. Passados **30 segundos**, deixa passar **uma** requisição para ir ver.
4. Se essa correr bem, o circuito fecha e tudo volta ao normal, sozinho.

O estado vive na cache de ficheiros, partilhada entre os processos PHP do
alojamento. Se vivesse no MySQL, caía com ele.

---

## O que **não** é protegido, de propósito

Escritas — candidaturas, registos, publicações, pagamentos, perfis — **não**
passam por aqui. Se o MySQL estiver em baixo, falham à vista, com um 503 e um
`Retry-After`. Fingir que uma candidatura foi entregue seria pior do que o
erro.

E um erro de programação continua a ser um 500. Só as falhas de **ligação**
viram a página calma; se tudo o que rebenta virasse "serviço indisponível",
uma avaria a sério ficava meses sem ninguém dar por ela. É por isso que o
`FalhaDeLigacao` olha para o **número** do erro MySQL (1226, 1040, 2002…) e não
para a classe do SQLSTATE: o 1226 vem com SQLSTATE 42000, que é a classe dos
erros de sintaxe.

---

## A cache de HTML

Guarda a página já desenhada e serve-a sem voltar a correr nada.

**Nunca entra na cache:**

- o que não for um GET com resposta 200 em HTML;
- páginas de quem tem sessão iniciada — a cópia é partilhada por todos;
- páginas com erros de formulário ou avisos em memória de sessão;
- endereços com parâmetros que não o `page` (pesquisas e filtros são infinitos);
- rotas fora da lista do `config/resiliencia.php` — é uma lista de
  autorizações, não de proibições, para que uma rota nova nunca entre por
  distracção.

**O token CSRF** é trocado por uma marca antes de guardar e reposto no momento
de servir. Sem isso, o token do primeiro visitante ficava na cópia e todos os
outros recebiam-no — os formulários ou rebentavam com 419, ou passavam a
partilhar um token, que é pior.

**O idioma entra na chave.** O site fala português e inglês na mesma morada;
sem isto, um visitante inglês deixava a página dele a toda a gente.

A cache é deitada fora inteira sempre que se publica ou edita uma vaga, um
artigo, uma categoria ou uma empresa.

### Sobre o LiteSpeed

**Analisado e não adoptado.** O LSCache guardaria a página no servidor, antes
do PHP — e aí a troca do token CSRF nunca acontecia, porque o PHP não chega a
correr. O primeiro visitante deixava o token dele a toda a gente.

Para o usar com segurança era preciso tirar o token do HTML e ir buscá-lo por
JavaScript depois de a página carregar. É possível, mas é outra mudança. Além
disso, não dá para confirmar daqui se o módulo LSCache sequer está activo nesta
conta — isso vê-se no cPanel, em **LiteSpeed Web Cache Manager**.

---

## O `.env`

O projecto é **Laravel 9**, por isso as variáveis são `CACHE_DRIVER` e
`SESSION_DRIVER` (o `CACHE_STORE` só existe a partir do Laravel 11).

```dotenv
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

Estes três já eram os valores por omissão do projecto — **confirme só que o
`.env` do servidor não os muda para `database`**:

```bash
grep -E "CACHE_DRIVER|SESSION_DRIVER|QUEUE_CONNECTION" .env
```

Se algum estiver em `database`, está a pendurar a rede de segurança na coisa
que se partiu. A `config/resiliencia.php` defende-se disso sozinha — se o
`CACHE_DRIVER` for `database`, a cache de resiliência volta ao sistema de
ficheiros à força — mas a sessão não se pode defender assim.

As variáveis novas são todas opcionais; os valores por omissão estão no
`config/resiliencia.php` e no `.env.example`.

---

## Verificar que está a funcionar

```bash
php artisan resiliencia
```

Mostra a loja de cache, os tempos, o estado do disjuntor, se o MySQL responde,
e quais as páginas que já têm cópia de emergência.

```bash
php artisan resiliencia --limpar   # deita fora as cópias normais; as de
                                   # emergência ficam
php artisan resiliencia --fechar   # fecha o disjuntor à força
```

Pela linha de comandos, numa página pública:

```bash
curl -s https://angolaemprego.com/vagas -o /dev/null     # aquece
curl -sI https://angolaemprego.com/vagas | grep -i x-cache-angola
```

Na primeira visita não aparece nada; na segunda:

```
X-Cache-Angola: hit
X-Cache-Angola-Em: 2026-10-15T18:35:06+00:00
```

O `-I` manda um **HEAD**, que é servido da cópia mas nunca a cria — por isso é
preciso aquecer com um GET antes. É de propósito: a resposta a um HEAD vai sem
corpo, e guardá-la enchia a cache de páginas vazias.

No `storage/logs/laravel.log`, tudo o que isto faz fica marcado com
`[resiliencia]`:

```bash
grep resiliencia storage/logs/laravel.log | tail -20
```

---

## Depois de publicar

Sempre que mudar o `.env` ou publicar código:

```bash
php artisan config:clear && php artisan cache:clear
```

A cache de HTML guarda a página tal como ela estava, incluindo o que depende da
configuração. Sem limpar, uma alteração ao `.env` pode demorar 10 minutos a
aparecer.

---

## O que não foi preciso mexer

- **Agendamentos.** O `app/Console/Kernel.php` não tem nenhum, por isso não há
  onde pôr `withoutOverlapping()`. Se vier a haver, é obrigatório. **Atenção:**
  isto é o que está no código — se houver *cron jobs* configurados no cPanel a
  chamar `artisan` ou URLs, esses não aparecem aqui e devem ser verificados à
  mão, porque execuções sobrepostas são uma das maneiras de esgotar as 30
  ligações.
- **Filas.** `QUEUE_CONNECTION=sync`: não há workers a segurar ligações.
- **Ligações persistentes.** O `config/database.php` não usa
  `PDO::ATTR_PERSISTENT`. Não as ligue: num alojamento partilhado, ligações
  persistentes esgotam o limite mais depressa, não menos.

---

## Desfazer

Tudo o que isto faz pode ser desligado sem apagar código.

**Só a cache de HTML**, mantendo a protecção da base de dados:

```dotenv
RESILIENCIA_HTML=false
```

**A rede toda**, voltando ao comportamento anterior:

```dotenv
RESILIENCIA_HTML=false
RESILIENCIA_NORMAL=1440
RESILIENCIA_EMERGENCIA=1
RESILIENCIA_FALHAS=999999
```

Seguido de `php artisan config:clear && php artisan cache:clear`. Com o
`RESILIENCIA_FALHAS` alto o disjuntor nunca abre, e com a emergência a 1 segundo
não há cópia a servir — o comportamento passa a ser o de antes, com a diferença
de que uma falha de ligação continua a dar 503 em vez de 500.

**Por commit:** o trabalho está num commit só. `git revert` nesse commit
desfaz tudo, incluindo o índice em `jobs.company` (a migração tem `down()`).

A correcção do `Schema::defaultStringLength` vale por si e não tem nada que ver
com cache — se desfizer o resto, vale a pena manter essa.

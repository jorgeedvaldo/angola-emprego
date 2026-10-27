# Editar artigos pela API

Como actualizar um artigo já publicado a partir de outro sistema — pelo `id`
ou pelo `slug`, à escolha.

```
PUT https://angolaemprego.com/api/posts/{identificador}
Content-Type: application/json
```

(`PATCH` funciona exactamente da mesma forma; a rota aceita os dois.)

Sem autenticação, como o resto desta API. Como está sob `/api`, leva o mesmo
limite de **60 pedidos por minuto** por endereço IP.

---

## O `{identificador}`

Aceita o `id` do artigo ou o seu `slug` — é a mesma rota para os dois:

```bash
# pelo id
curl -X PUT https://angolaemprego.com/api/posts/42 \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"description": "<p>Texto revisto.</p>"}'

# pelo slug
curl -X PUT https://angolaemprego.com/api/posts/como-escrever-um-bom-curriculo \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"description": "<p>Texto revisto.</p>"}'
```

Se o identificador for só dígitos, é tentado primeiro como `id`; só passa a
procurar por `slug` se não encontrar nenhum artigo com esse id. Isto cobre o
caso raro de um artigo cujo título gera um slug só de números.

---

## Os campos

Envie só os que quer mudar — o que não enviar fica como estava.

| Campo | Nota |
|---|---|
| `title` | o título |
| `description` | aceita HTML, como na criação |
| `image` | caminho da imagem; ver o aviso abaixo |
| `slug` | **o endereço do artigo** — ver a secção seguinte antes de o usar |

Pelo menos um destes tem de vir no pedido, senão a resposta é `422`:

```json
{ "message": "Nada para actualizar. Envie pelo menos um destes campos: title, slug, description, image." }
```

Campos fora desta lista (como `id` ou `created_at`) são ignorados, não dão
erro.

**Sobre `image`:** tal como na criação, não é tratada como um ficheiro — é
guardado o que vier nesse campo tal como está. Só faz sentido se já for um
caminho válido dentro do disco público do site.

---

## O `slug` é o endereço — não muda sozinho

**Mudar o `title` nunca muda o `slug`.** O slug é o que está na morada do
artigo (`/noticias/{slug}`); mudá-lo às escondidas partia qualquer ligação já
partilhada ou indexada pelo Google.

```bash
# isto só muda o título — a morada do artigo fica exactamente a mesma
curl -X PUT https://angolaemprego.com/api/posts/42 \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"title": "Um título novo e melhor"}'
```

Se quiser mesmo mudar a morada, envie `slug` explicitamente:

```bash
curl -X PUT https://angolaemprego.com/api/posts/42 \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"slug": "novo-endereco-do-artigo"}'
```

O que acontece a esse valor:

- Passa por uma limpeza automática (`Str::slug`) — maiúsculas, acentos e
  espaços saem; `"Notícia de Última Hora!"` vira `noticia-de-ultima-hora`.
- Tem de ser único. Se outro artigo já o usa, a resposta é `422`:
  ```json
  { "message": "Já existe um artigo com este slug.", "slug": "o-que-tentou" }
  ```
- Não pode ficar vazio depois da limpeza (um pedido com `"slug": "!!!"`
  também dá `422`).

---

## Resposta

`200`, com o artigo completo depois da alteração:

```json
{
  "id": 42,
  "title": "Como organizar a sua biblioteca técnica",
  "slug": "como-organizar-a-sua-biblioteca-tecnica",
  "description": "<p>Texto revisto.</p>",
  "image": "images/posts/....png",
  "created_at": "...",
  "updated_at": "..."
}
```

## Erros

| Estado | Quando |
|---|---|
| `404` | não existe artigo com esse id ou slug |
| `422` | corpo vazio, slug vazio, ou slug já usado por outro artigo |

---

## ⚠️ Uma avaria encontrada à parte, sem relação com isto

A rota de **criar** artigos (`POST /api/post/create`, já existente) está
partida sempre que a coluna `posts.slug` é tratada em modo estrito — que é a
omissão do MySQL desde a versão 5.7. A tabela não tem valor por omissão para
`slug`, e o `Post::create()` dessa rota nunca o envia (o slug só é calculado
*depois* de o registo já estar gravado). Em MySQL sem modo estrito isto
passa silenciosamente, criando por instantes um artigo com `slug` vazio; com
modo estrito, falha com `500`.

Não foi corrigido — não fazia parte deste pedido. Fica aqui registado.

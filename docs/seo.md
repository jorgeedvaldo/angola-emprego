# O cabeçalho de SEO

O que o site escreve no `<head>` de cada página, porquê, e onde se mexe nisso.

É o mesmo trabalho que um plugin como o Yoast faz num WordPress. A diferença é
que aqui está em código nosso: `app/Support/Seo.php` e o `<head>` do
`resources/views/templates/app.blade.php`.

---

## Como uma página declara o que é

As vistas não escrevem etiquetas. Declaram o que sabem, e o cabeçalho trata do
resto:

```blade
@extends('templates.app')
@section('title', $post->title)
@section('description', Str::limit(strip_tags($post->description), 160))
@section('canonical_link', url('/noticias/' . $post->slug))
@section('og_type', 'article')
@section('og_image', asset('storage/' . $post->image))
@section('schema_tipo', 'CollectionPage')      {{-- só nas listagens --}}
@section('robots', \App\Support\Seo::ROBOTS_ESCONDIDO)   {{-- só nas privadas --}}
```

Nenhuma é obrigatória. Sem `canonical_link` a página aponta para si própria;
sem `og_image` usa a imagem por omissão; sem `robots` decide-se sozinho.

---

## O que cada peça faz

### `<link rel="canonical">`

Diz qual é o endereço verdadeiro da página. Sem ele, a mesma página com
`?fbclid=` (vem do Facebook), com `?gclid=` (vem do Google Ads) ou com uma barra
a mais conta como páginas diferentes com o mesmo conteúdo — e a força que devia
estar junta num endereço fica dividida por cinco.

**Todas as páginas têm um.** As que não o declaram apontam para si próprias.

### `<meta name="robots">`

Duas respostas possíveis:

| | Quando |
|---|---|
| `index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1` | páginas normais. O `max-snippet:-1` e o `max-image-preview:large` autorizam o Google a mostrar um trecho e uma imagem grande nos resultados — sem isso mostra menos |
| `noindex, follow, max-image-preview:large` | "não guardes esta página, mas segue os links que estão nela" |

O `noindex` é automático quando o endereço traz parâmetros — uma pesquisa
(`/vagas?q=contabilidade`) tem o conteúdo de outra página e há infinitas. O
`?page=` é a excepção: a página 2 de uma listagem é conteúdo a sério.

Nas páginas privadas (entrar, registar, painel da empresa, perfil, planos,
pagamento, erros) está declarado à mão com `@section('robots', ...)`.

### O nome do site

O que o Google escreve por cima do resultado, em vez do domínio.

Vem de `config/seo.php` (`SEO_SITE_NAME` no `.env`), **não** do `APP_NAME`. O
`APP_NAME` assina também os emails e acaba quase sempre com o slogan colado —
e um nome com slogan o Google recusa, voltando a escrever `angolaemprego.com`.

Três sítios têm de dizer o mesmo, ou o Google ignora os três:

| | |
|---|---|
| `WebSite.name` no JSON-LD | o principal, e **só é lido da página inicial** |
| `og:site_name` | o que as redes sociais mostram |
| o fim do `<title>` | o sinal de reserva |

A forma comprida vai para o `alternateName` (`SEO_SITE_ALT_NAME`), que é onde o
Google a aceita sem a confundir com o nome.

Depois de mudar, o nome só aparece quando o Google voltar a ler a **página
inicial** — pode levar semanas. Pedir a indexação da página inicial no Search
Console acelera.

### Open Graph e Twitter

O que o Facebook, o LinkedIn e o WhatsApp lêem quando alguém partilha a ligação.

A medida da imagem (`og:image:width`, `og:image:height`, `og:image:type`) é
**medida no ficheiro**, não adivinhada. Estas redes acreditam no que lhes
dizemos: a medida errada faz a imagem sair cortada ou não sair de todo. Antes
estava escrito 1200x630 para toda a gente, incluindo para a imagem por omissão,
que é quadrada.

De uma imagem que não esteja no nosso `public/` não se sabe nada — nesse caso
não se diz nada, que é melhor do que dizer mal.

### `twitter:label1` / `twitter:data1`

O "tempo estimado de leitura" dos artigos, a 200 palavras por minuto.

### Os dados estruturados

Um grafo só, com as peças ligadas por `@id`:

```
WebSite  ──publisher──▶  Organization
   ▲
   └──isPartOf── WebPage ──about──▶ Organization
                    │
                    └──primaryImageOfPage──▶ ImageObject
```

A ligação é o que importa. O Google lê "esta página faz parte deste site, que é
publicado por esta organização". Fichas soltas, sem `@id`, são três coisas sem
relação nenhuma entre si.

O `WebSite` traz também a `SearchAction`, que é a caixa de pesquisa que o Google
pode mostrar por baixo do site nos resultados.

Cada página acrescenta a sua ficha por cima: `JobPosting` numa vaga,
`NewsArticle` num artigo, `CollectionPage` numa listagem, `FAQPage` no Sobre.
**Uma só por endereço** — duas fichas de artigo para a mesma página são duas
entidades em conflito, e o `SeoTest` trava isso.

Quando a página escreve a sua própria ficha para o mesmo endereço (a
`CollectionPage` das listagens, a `FAQPage` do Sobre), declara `@section('schema_tipo')`
e o mesmo `@id` — assim as duas são a mesma entidade, não duas páginas no mesmo
sítio.

---

## O resto

- **`/sitemap.xml`** — todas as vagas, artigos, cursos, empresas e categorias.
  Indicado no `public/robots.txt`.
- **`/feed`** — o feed geral. Cada vaga e cada artigo tem também o seu
  (`/vagas/{slug}/feed`), apontado no cabeçalho com `<link rel="alternate">`.
  Ver `docs/` e o `FeedController`.

---

## O que ainda não fazemos, e porquê

- **`hreflang`.** O site tem português e inglês, mas o idioma vive na sessão, não
  no endereço: `/vagas` é o mesmo endereço nas duas línguas. Sem endereços
  separados não há `hreflang` possível, e **o Google só vê a versão portuguesa**.
  Para o inglês contar é preciso pô-lo no endereço (`/en/vagas` ou
  `en.angolaemprego.com`), que é uma mudança de fundo.

- **`rel="prev"` / `rel="next"`.** O Google deixou de os usar em 2019; o Bing
  ainda os lê. Como o sitemap já leva todas as vagas, não se perde nada.

- **O `robots.txt` bloqueia as pesquisas internas.** Agora que essas páginas
  pedem `noindex` sozinhas, o bloqueio passou a ser contraproducente: uma página
  bloqueada não chega a ser lida, por isso o `noindex` dela nunca é visto, e um
  endereço que já esteja no índice lá fica. Desbloquear é decisão de quem manda
  no site — o ficheiro é `public/robots.txt`.

- **O canónico das listagens paginadas** aponta sempre para a página 1
  (`@section('canonical_link', url('/vagas'))` em `jobs.blade.php`). É uma
  escolha defensável com um sitemap completo, mas quem quiser que as páginas 2 e
  3 contem por si tira essa linha.

---

## Onde partir isto sem dar por ela

1. **Escrever `env('APP_NAME')` numa vista.** Em produção a configuração está em
   cache e o `env()` devolve `null` — todos os títulos ficariam a acabar em
   `" - "`. Usa-se `config('app.name')`.
2. **Um `@json([...])` escrito em várias linhas** não compila no Blade. O array
   monta-se num bloco `@php` e o `@json($variavel)` fica numa linha só.
3. **Acrescentar uma segunda ficha de artigo ou de página** ao mesmo endereço.

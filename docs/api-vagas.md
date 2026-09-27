# Publicar vagas pela API

Como publicar uma vaga no Angola Emprego a partir de outro sistema — em
particular, como publicar **vagas do Brasil**, que é o que mudou agora.

```
POST https://angolaemprego.com/api/job/create
Content-Type: application/json
```

Sem autenticação. O limite é de **60 pedidos por minuto** por endereço IP; acima
disso a resposta é `429`.

---

## O essencial: publicar para o Brasil

O que mudou é um campo só — o `country`. Tudo o resto continua igual ao que já
enviava.

```bash
curl -X POST https://angolaemprego.com/api/job/create \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Analista de Suporte Técnico",
    "company": "TechBrasil Serviços",
    "location": "São Paulo, SP",
    "description": "<p>Requisitos:</p><ul><li>Ensino médio completo</li></ul>",
    "email_or_link": "vagas@techbrasil.com.br",
    "country": "BR"
  }'
```

Resposta (`200`), com a vaga já criada:

```json
{
  "id": 1841,
  "title": "Analista de Suporte Técnico",
  "slug": "analista-de-suporte-tecnico",
  "company": "TechBrasil Serviços",
  "location": "São Paulo, SP",
  "country_id": 2,
  "image": "images/jobs/4741523a-8989-4406-b2b2-ef0a3b1bfc7f.png",
  "country": { "id": 2, "name": "Brasil", "name_en": "Brazil", "code": "BR" }
}
```

A vaga fica em `https://angolaemprego.com/vagas/{slug}`.

---

## Os campos

| Campo | Obrigatório | Notas |
|---|---|---|
| `title` | **sim** | O `slug` do endereço sai daqui, gerado automaticamente |
| `company` | **sim** | Nome da empresa, em texto |
| `location` | **sim** | Cidade e estado. Nas vagas do Brasil **aparece na imagem de partilha** |
| `description` | **sim** | Aceita HTML (`<p>`, `<ul>`, `<li>`, `<strong>`) |
| `email_or_link` | **sim** | Para onde o candidato se candidata |
| `country` | não | Código ISO, ou o nome. **Sem este campo a vaga fica para Angola** |
| `country_code` | não | O mesmo que `country`; existe só por conveniência |
| `country_id` | não | O id do país, se o preferir ao código |

**Não envie `image`.** A capa é desenhada pelo site quando a vaga é criada. Se
mandar esse campo, o site guarda o que lá puser em vez de gerar a capa, e a vaga
fica com uma imagem partida.

### Formas de dizer que a vaga é do Brasil

Todas estas funcionam, escolha a que lhe der mais jeito:

```json
{ "country": "BR" }          { "country": "br" }
{ "country": "Brasil" }      { "country": "Brazil" }
{ "country_code": "BR" }     { "country_id": 2 }
```

Para os outros países é igual, com o respectivo código ISO: `AO` Angola,
`ES` Espanha, `MX` México, `AR` Argentina, `CO` Colômbia, `PE` Peru, `CL` Chile…
São os 23 da lista abaixo, e só esses — **Portugal, por exemplo, ainda não está
lá**, e um pedido com `"country": "PT"` devolve `422`.

<details>
<summary>Os 23 países aceites</summary>

| Código | País | Código | País |
|---|---|---|---|
| `AO` | Angola | `GT` | Guatemala |
| `BR` | Brasil | `GQ` | Guiné Equatorial |
| `AR` | Argentina | `HN` | Honduras |
| `BO` | Bolívia | `MX` | México |
| `CL` | Chile | `NI` | Nicarágua |
| `CO` | Colômbia | `PA` | Panamá |
| `CR` | Costa Rica | `PY` | Paraguai |
| `CU` | Cuba | `PE` | Peru |
| `SV` | El Salvador | `PR` | Porto Rico |
| `EC` | Equador | `DO` | República Dominicana |
| `ES` | Espanha | `UY` | Uruguai |
|  |  | `VE` | Venezuela |

Para acrescentar um país à lista, veja `database/data/paises.php`.

</details>

---

## A capa da vaga do Brasil

A imagem de partilha das vagas do Brasil é diferente das de Angola: fundo verde,
destaques a amarelo, um letreiro **VAGA PARA BRASIL** no topo e, por baixo do
título, a **localização da vaga**.

Duas consequências práticas para quem publica:

- O `location` passa a aparecer na imagem, não só na página. Vale a pena mandar
  algo legível — `"São Paulo, SP"` e não `"SP"` ou `"Brasil"`. Moradas muito
  compridas são cortadas com reticências.
- A capa é desenhada no momento em que a vaga é criada. Se publicar uma vaga com
  o país errado e corrigir depois, a capa **não** é redesenhada.

---

## Erros

### `422` — país desconhecido

```json
{
  "message": "País desconhecido. Use o código ISO de dois dígitos (AO, BR, ES...) ou o nome do país.",
  "country": "BRA"
}
```

Acontece com códigos de três letras (`BRA`), nomes mal escritos ou países que
não estão na lista. **A vaga não é criada.** É de propósito: uma vaga do Brasil
a sair em silêncio para Angola por causa de um código mal escrito seria pior do
que o erro, porque ninguém daria por ela.

### `500` — falta um campo obrigatório

Se faltar `company`, `location`, `description` ou `email_or_link`, a resposta é
um `500 Server Error` genérico, sem dizer qual o campo em falta. É uma aspereza
conhecida desta rota, que nunca teve validação. Ao integrar, confirme os cinco
campos obrigatórios do seu lado antes de enviar.

---

## Apagar uma vaga

```
DELETE https://angolaemprego.com/api/jobs/{id}
X-API-Key: <a sua chave>
```

É a **única rota desta API que pede chave**, e a única que não se desfaz.
Publicar uma vaga a mais é chato mas corrige-se; apagar não. Sem a chave, ou
com a chave errada, responde `401` e não apaga nada.

A chave vive no `.env` do servidor, em `API_KEY`. Gere uma comprida e ao acaso:

```bash
php -r "echo bin2hex(random_bytes(32));"
```

Se o servidor não tiver `API_KEY` configurada, a rota responde `503` a toda a
gente — fechada é melhor do que aberta por esquecimento.

```bash
curl -X DELETE https://angolaemprego.com/api/jobs/1841 \
  -H "X-API-Key: $API_KEY" \
  -H "Accept: application/json"
```

```json
{
  "message": "Vaga apagada.",
  "deleted": {
    "id": 1841,
    "title": "Analista de Suporte Técnico",
    "slug": "analista-de-suporte-tecnico",
    "applications": 0,
    "files_removed": 2
  }
}
```

### Vagas com candidaturas

Uma vaga com candidaturas **não se apaga à primeira**. São CVs que pessoas
enviaram e que desaparecem com ela, e um script não deve poder destruí-los sem
o pedir de propósito. A resposta é `409`:

```json
{
  "message": "Esta vaga tem 7 candidatura(s), que são apagadas com ela. Repita com 'force': true para confirmar.",
  "job_id": 1841,
  "applications": 7
}
```

Para confirmar:

```bash
curl -X DELETE https://angolaemprego.com/api/jobs/1841 \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"force": true}'
```

### O que desaparece com a vaga

| | |
|---|---|
| Candidaturas e os seus anexos | as linhas descem em cascata na base de dados |
| CVs enviados pelos candidatos | a pasta `job-applications/{id}` sai do disco |
| Capa da vaga e a miniatura | saem do disco |
| Ligação às categorias | a ligação sai; **as categorias em si ficam** |

As candidaturas descem em cascata, mas os ficheiros no disco não: ninguém mais
iria lá buscá-los, por isso é o endpoint que os remove. O `files_removed` da
resposta diz quantos saíram.

### Erros

| Estado | Quando |
|---|---|
| `401` | chave em falta ou errada |
| `404` | não existe vaga com esse id — apagar duas vezes dá `404` à segunda |
| `409` | a vaga tem candidaturas e não veio `force` |
| `503` | o servidor não tem `API_KEY` configurada |

Limite: 30 pedidos por minuto.

---

## O que esta rota ainda não faz

- **Categorias.** O campo `categories` é ignorado; as vagas publicadas por aqui
  ficam sem categoria e têm de ser categorizadas no site.
- **Actualizar.** Só se cria e se apaga. Cada `POST` cria uma vaga nova, mesmo
  que o título seja igual ao de outra.
- **Autenticação para criar.** O `POST /api/job/create` continua aberto; só o
  apagar pede chave.

Para ler uma vaga já criada: `GET /api/jobs/{id}`, que devolve também as
categorias e o país.

---

## Exemplo em PHP

```php
$vaga = [
    'title' => 'Analista de Suporte Técnico',
    'company' => 'TechBrasil Serviços',
    'location' => 'São Paulo, SP',
    'description' => '<p>Requisitos:</p><ul><li>Ensino médio completo</li></ul>',
    'email_or_link' => 'vagas@techbrasil.com.br',
    'country' => 'BR',
];

$ch = curl_init('https://angolaemprego.com/api/job/create');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
    CURLOPT_POSTFIELDS => json_encode($vaga, JSON_UNESCAPED_UNICODE),
]);

$resposta = curl_exec($ch);
$estado = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($estado !== 200) {
    // 422 traz a explicação em 'message'; 500 quer dizer campo obrigatório em falta.
    throw new RuntimeException("A vaga não foi publicada (HTTP $estado): $resposta");
}
```

## Exemplo em Python

```python
import requests

vaga = {
    "title": "Analista de Suporte Técnico",
    "company": "TechBrasil Serviços",
    "location": "São Paulo, SP",
    "description": "<p>Requisitos:</p><ul><li>Ensino médio completo</li></ul>",
    "email_or_link": "vagas@techbrasil.com.br",
    "country": "BR",
}

r = requests.post(
    "https://angolaemprego.com/api/job/create",
    json=vaga,
    headers={"Accept": "application/json"},
    timeout=30,
)

if r.status_code != 200:
    raise RuntimeError(f"A vaga não foi publicada (HTTP {r.status_code}): {r.text}")

print("Publicada:", "https://angolaemprego.com/vagas/" + r.json()["slug"])
```

# Analisador de CV: motor de pontuação

O analisador público (`/analisador-de-cv`, sem conta) pode pontuar os CVs de
duas maneiras. Troca-se por uma linha do `.env`:

```dotenv
CV_ANALYZER_ENGINE=vectores   # o de sempre
CV_ANALYZER_ENGINE=jev        # pergunta ao JEV
```

**Para voltar atrás, apague a linha.** O caminho dos vectores não foi tocado —
está no mesmo sítio, corre exactamente como corria, e os testes que o cobrem
continuam a passar. Não há nada para desfazer.

A análise de candidaturas das empresas, que grava pontuações na base de dados,
**nunca** passa por aqui: fica sempre nos vectores.

## O que é o JEV, e o que não é

O JEV não é um LLM de conversa. **Não gera texto e não responde a um prompt em
linguagem livre.** É um modelo "System One": recebe um *state* — o material a
avaliar — e perguntas tipadas, e devolve valores tipados, com probabilidades
calibradas.

Tem três tipos de pergunta:

| Tipo | Pergunta | Resposta |
|---|---|---|
| **Noul** | "Isto é verdade?" | uma probabilidade entre 0 e 1 |
| **Choice** | "Qual destas opções?" | a opção escolhida + probabilidade de cada uma |
| **Score** | "Onde fica nesta escala?" | uma posição entre 2 e 10 níveis, possivelmente fraccionária |

Usamos um **Noul**: a proposição é "este candidato cumpre a vaga e deve ser
chamado para entrevista", e a probabilidade que o JEV devolve **é** a
percentagem de compatibilidade, sem conversões pelo meio. Foi por isso que se
escolheu o Noul e não o Score — um score de 0 a 4 teria de ser reescalado, e o
reescalamento é onde se perde a calibração.

## A metodologia

1. O recrutador escreve a descrição da vaga. Com o JEV ligado **não há passo de
   vectorização**: a descrição volta tal e qual para o browser.
2. Cada CV é enviado para o `analisecv`, que faz o **OCR** e devolve o texto. O
   JEV não lê PDFs — continua a ser preciso ter o `ANALISECV_URL` configurado,
   mesmo com o JEV ligado.
3. A descrição da vaga e o texto do CV vão juntos, no mesmo pedido, como o
   *state* da pergunta.
4. A probabilidade que volta é a pontuação. A lista reordena-se da maior para a
   menor, como já fazia.

## O que se perde com o JEV ligado

A lista de requisitos cumpridos e as palavras-chave encontradas **deixam de
aparecer**. O JEV devolve um número, não uma justificação; dizer ao recrutador
que requisitos foram cumpridos seria inventar. Se essa lista fizer falta, é
argumento para ficar nos vectores.

## Quando alguma coisa corre mal

Um erro do JEV aparece como erro ao recrutador, e **nunca como zero por cento**
— um zero faria passar por mau um candidato sobre o qual, na verdade, não se
sabe nada.

Se pedir `CV_ANALYZER_ENGINE=jev` sem a `JEV_API_KEY` configurada, o analisador
cai no motor antigo em vez de ficar em baixo.

## O pedido e a resposta

`POST https://api.typesafe.ai/v1/systemone`, com `Authorization: Bearer <chave>`.

```json
{
  "model": "jev-latest",
  "state": {
    "vaga": "Técnico de Recursos Humanos. Requisitos: licenciatura em RH...",
    "curriculo": "João Silva. Licenciado em Gestão de Recursos Humanos..."
  },
  "questions": {
    "compatibilidade": {
      "type": "noul",
      "instructions": "O candidato descrito em `curriculo` cumpre os requisitos da vaga descrita em `vaga`.",
      "criteria": { "true": "...", "false": "..." }
    }
  }
}
```

```json
{
  "model": "jev-1.13.0",
  "answers": { "compatibilidade": { "type": "noul", "noul": 0.81 } },
  "usage": { "input_tokens": 296, "output_tokens": 20 }
}
```

`0.81` → **81%**. É esse o número que o recrutador vê, e é por ele que a lista
se ordena.

A pergunta tem **uma condição só**, como a documentação insiste: juntar "cumpre
os requisitos **e** deve ser entrevistado" seriam duas perguntas disfarçadas de
uma, e o valor significaria menos. Está escrita de maneira a que um valor alto
signifique sim — ao contrário, quem lesse a pontuação mais tarde entendia-a ao
contrário.

## Limites e custos

| | |
|---|---|
| Contexto | 64k tokens por pedido; 32k para o `state` mais a pergunta mais longa |
| Limite de pedidos | 1200 por minuto — folgado para os 30 CVs do analisador |
| Preço | $0,042 por milhão de tokens de entrada; a saída não se paga |

A vaga é cortada aos 20 mil caracteres e o CV aos 30 mil, com folga larga sobre
o limite do `state`. Serve para o caso extremo — um CV digitalizado de vinte
páginas — e não para o normal, em que um CV não chega a seis mil caracteres.

Um `429` (limite de pedidos) ou um `529` (sobrecarga) são repetidos até três
vezes, respeitando o `retry-after` quando ele vem. Um `401` ou um `422` não são
repetidos: não passam por muito que se insista.

## ⚠️ O aviso que mais pesa aqui: a língua

A documentação do modelo é explícita:

> English is the primary training language and where accuracy is currently
> best. Other languages, including CJK scripts, are handled but not equally
> well; **test on your own content before relying on Jev for a non-English
> workload.**

As vagas e os CVs deste portal são em português. O JEV aceita-os, mas a
precisão em português não é a mesma que em inglês, e isso não se sabe sem medir.

**Por isso não ligue o JEV em produção sem comparar primeiro.** Pegue numa vaga
real e num punhado de CVs que já conheça — os bons e os maus — e corra as duas
vezes, com `CV_ANALYZER_ENGINE=vectores` e com `=jev`. Se o JEV não separar os
bons dos maus melhor do que os vectores, apague a linha e fica tudo como estava.

O alias `jev-latest` muda quando sai uma versão nova do modelo, e com ele mudam
as pontuações. Se afinar limiares contra uma versão, fixe-a: `JEV_MODEL=jev-1.13.0`.

## Se os resultados não chegarem

Antes de desistir, há um passo intermédio que a documentação recomenda
("composite scoring"): em vez de uma pergunta vaga, várias perguntas atómicas
no **mesmo pedido** — tem a experiência? tem a formação? tem os certificados? —
combinadas em código com pesos seus. As perguntas correm em paralelo, por isso
três perguntas custam quase o mesmo que uma. Isso também devolveria ao
recrutador a explicação que hoje se perde.

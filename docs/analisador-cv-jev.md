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

## ⚠️ Por confirmar

O `docs.typesafe.ai` está bloqueado pela política de saída da rede do ambiente
onde isto foi escrito, em todos os caminhos e por todas as ferramentas. Por
isso **o endereço, o cabeçalho de autenticação e os nomes dos campos do pedido
e da resposta não foram confirmados contra a documentação.**

O que se sabe é o modelo (state + perguntas tipadas, Noul devolve 0–1); o que
falta é a forma exacta do HTTP.

Para reduzir o custo de estar errado:

- tudo o que é "wire" está em `config/services.php` e sai do `.env`, por isso
  corrige-se sem tocar no código:

  ```dotenv
  JEV_URL=https://api.typesafe.ai
  JEV_ENDPOINT=/v1/evaluate
  JEV_MODEL=jev-latest
  ```

- a leitura da resposta procura a probabilidade em vários sítios plausíveis e,
  quando não a encontra, **regista o corpo inteiro no log**. Na primeira
  chamada real vê-se lá exactamente o que o JEV devolveu, e corrige-se num sítio
  só — `JevClient::probabilidade()`.

Confirmar contra `docs.typesafe.ai/api`, `docs.typesafe.ai/primitives/noul` e
`docs.typesafe.ai/models`.

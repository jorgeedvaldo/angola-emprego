<?php

namespace App\Services\Cv;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cliente do JEV, o modelo "System One" da TypeSafe.
 *
 * O JEV não é um LLM de conversa: não gera texto e não responde a um prompt em
 * linguagem livre. Recebe um "state" — o material a avaliar — e perguntas
 * tipadas, e devolve valores tipados.
 *
 * A pergunta que aqui se faz é um *noul*: uma proposição de sim/não cuja
 * resposta é a probabilidade de ser verdadeira, entre 0 e 1. Essa probabilidade
 * é, tal e qual, a percentagem de compatibilidade que o recrutador vê — e é
 * também o padrão que a própria documentação recomenda para ordenar candidatos
 * ("Re-ranking": um noul por par, depois ordena-se pelo valor).
 *
 * Contrato: POST https://api.typesafe.ai/v1/systemone
 *   pedido   { state, model, questions: { <id>: {type, instructions, criteria} } }
 *   resposta { model, answers: { <id>: {type: "noul", noul: 0.0-1.0} }, usage }
 */
class JevClient
{
    /** O id da pergunta. Escolhido por nós; a resposta volta debaixo dele. */
    private const PERGUNTA = 'compatibilidade';

    /**
     * Cortes do texto que vai no state.
     *
     * O modelo aceita 32k tokens para o state mais a pergunta mais longa. Em
     * português conta-se à volta de três a quatro caracteres por token, por isso
     * estes limites (50 mil caracteres ao todo) ficam com folga larga. Servem
     * para o caso extremo — um CV digitalizado de vinte páginas — e não para o
     * caso normal, em que um CV não chega a seis mil caracteres.
     */
    private const MAX_VAGA = 20000;
    private const MAX_CV = 30000;

    /** Estados que vale a pena repetir, como a documentação recomenda. */
    private const REPETIVEIS = [429, 529];

    public function configurado(): bool
    {
        return (bool) config('services.jev.api_key');
    }

    /**
     * Pergunta ao JEV quanto é que um CV combina com uma vaga.
     *
     * @return float|null  0..1, ou null quando não foi possível obter resposta
     */
    public function compatibilidade(string $descricaoVaga, string $textoCv): ?float
    {
        if (!$this->configurado()) {
            Log::warning('JevClient: JEV_API_KEY em falta.');

            return null;
        }

        $resposta = $this->perguntar([
            'model' => config('services.jev.model'),
            'state' => [
                'vaga' => $this->cortar($descricaoVaga, self::MAX_VAGA),
                'curriculo' => $this->cortar($textoCv, self::MAX_CV),
            ],
            'questions' => [
                self::PERGUNTA => $this->pergunta(),
            ],
        ]);

        return $resposta === null ? null : $this->lerNoul($resposta);
    }

    /**
     * A pergunta.
     *
     * Uma condição só, como a documentação insiste: "Ask one yes/no question per
     * Noul. If a question has two conditions ... the model has to judge both at
     * once and the value means less." Juntar aqui "cumpre os requisitos e deve
     * ser entrevistado" seriam duas perguntas disfarçadas de uma.
     *
     * Está escrita de maneira a que um valor alto signifique sim — se fosse ao
     * contrário, quem lesse a pontuação mais tarde entendia-a ao contrário.
     *
     * @return array<string, mixed>
     */
    private function pergunta(): array
    {
        return [
            'type' => 'noul',
            'instructions' => 'O candidato descrito em `curriculo` cumpre os requisitos da vaga descrita em `vaga`.',
            'criteria' => [
                'true' => 'A experiência, a formação e as competências do candidato correspondem ao que a vaga pede.',
                'false' => 'O candidato é de outra área, ou falta-lhe um requisito que a vaga exige. '
                    . 'Um currículo bem escrito mas de outra profissão não cumpre a vaga.',
            ],
        ];
    }

    private function cortar(string $texto, int $maximo): string
    {
        $texto = trim($texto);

        return mb_strlen($texto) > $maximo ? mb_substr($texto, 0, $maximo) : $texto;
    }

    /**
     * @param  array<string, mixed>  $corpo
     * @return array<string, mixed>|null
     */
    private function perguntar(array $corpo): ?array
    {
        $url = rtrim((string) config('services.jev.url'), '/')
            . '/' . ltrim((string) config('services.jev.endpoint'), '/');

        $tentativas = (int) config('services.jev.tentativas', 3);

        for ($tentativa = 1; $tentativa <= $tentativas; $tentativa++) {
            try {
                $resposta = Http::withToken((string) config('services.jev.api_key'))
                    ->acceptJson()
                    ->timeout((int) config('services.jev.timeout', 60))
                    ->post($url, $corpo);
            } catch (\Throwable $excepcao) {
                Log::error('JevClient: falha ao contactar o JEV.', ['message' => $excepcao->getMessage()]);

                return null;
            }

            if ($resposta->successful()) {
                return $resposta->json();
            }

            // 429 (limite de pedidos) e 529 (sobrecarga) passam com uma espera;
            // um 401 ou um 422 não passam por muito que se insista.
            if (!in_array($resposta->status(), self::REPETIVEIS, true) || $tentativa === $tentativas) {
                Log::error('JevClient: o JEV rejeitou o pedido.', [
                    'status' => $resposta->status(),
                    'body' => $resposta->body(),
                ]);

                return null;
            }

            $this->esperar($resposta->header('retry-after'), $tentativa);
        }

        return null;
    }

    /**
     * Espera o que o servidor pedir; na falta disso, o dobro de cada vez.
     */
    private function esperar(?string $retryAfter, int $tentativa): void
    {
        $segundos = is_numeric($retryAfter) ? (int) $retryAfter : (2 ** $tentativa);

        usleep(min($segundos, 8) * 1000000);
    }

    /**
     * Tira a probabilidade da resposta.
     *
     * @param  array<string, mixed>  $resposta
     */
    private function lerNoul(array $resposta): ?float
    {
        $valor = $resposta['answers'][self::PERGUNTA]['noul'] ?? null;

        if (!is_numeric($valor)) {
            Log::error('JevClient: resposta do JEV sem o noul que se esperava.', ['body' => $resposta]);

            return null;
        }

        $valor = (float) $valor;

        if ($valor < 0.0 || $valor > 1.0) {
            Log::warning('JevClient: noul fora do intervalo 0-1; cortado.', ['valor' => $valor]);
        }

        return max(0.0, min(1.0, $valor));
    }
}

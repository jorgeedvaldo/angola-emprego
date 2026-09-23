<?php

namespace App\Services\Cv;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cliente do JEV, o modelo "System One" da TypeSafe.
 *
 * O JEV não é um LLM de conversa: não gera texto e não responde a um prompt em
 * linguagem livre. Recebe um "state" — o material a avaliar — e perguntas
 * tipadas, e devolve valores tipados. A pergunta que aqui se faz é um *noul*:
 * uma proposição de sim/não cuja resposta é a probabilidade de ser verdadeira,
 * entre 0 e 1. Essa probabilidade é, tal e qual, a percentagem de
 * compatibilidade que o recrutador vê.
 *
 * ATENÇÃO — o endereço, o cabeçalho de autenticação e os nomes dos campos do
 * pedido e da resposta NÃO foram confirmados contra a documentação: o
 * docs.typesafe.ai está bloqueado pela política de saída da rede deste
 * ambiente. Por isso tudo o que é "wire" está em config/services.php (jev.url,
 * jev.endpoint, jev.model) e a leitura da resposta procura o valor em vários
 * sítios plausíveis, registando o corpo recebido quando não o encontra — na
 * primeira chamada real vê-se no log exactamente o que o JEV devolve e
 * corrige-se num sítio só. Confirmar contra docs.typesafe.ai/api.
 */
class JevClient
{
    /** Um CV inteiro e uma vaga inteira num só state; corta-se o que passar. */
    public const MAX_CARACTERES_ESTADO = 60000;

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

        $resposta = $this->perguntar(
            $this->estado($descricaoVaga, $textoCv),
            $this->pergunta()
        );

        return $resposta === null ? null : $this->probabilidade($resposta);
    }

    /**
     * O material que o JEV avalia: a vaga e o CV, separados e identificados,
     * para o modelo saber o que é o pedido e o que é o candidato.
     *
     * @return array<string, string>
     */
    private function estado(string $descricaoVaga, string $textoCv): array
    {
        return [
            'vaga' => $this->cortar($descricaoVaga),
            'curriculo' => $this->cortar($textoCv),
        ];
    }

    /**
     * A pergunta. É um noul — uma proposição de sim/não — porque a
     * probabilidade que o JEV devolve já é a percentagem que queremos, sem
     * conversões pelo meio.
     *
     * @return array<string, mixed>
     */
    private function pergunta(): array
    {
        return [
            'id' => 'compatibilidade',
            'type' => 'noul',
            'question' => 'O candidato descrito em "curriculo" cumpre os requisitos da vaga descrita em "vaga" '
                . 'e deve ser chamado para entrevista.',
            'criteria' => [
                'Conta a experiência profissional relevante para o cargo pedido.',
                'Conta a formação, os certificados e as competências técnicas que a vaga exige.',
                'Um currículo de outra área, ainda que bem escrito, não cumpre a vaga.',
                'A falta de um requisito obrigatório pesa mais do que a presença de requisitos desejáveis.',
            ],
        ];
    }

    private function cortar(string $texto): string
    {
        $texto = trim($texto);

        return mb_strlen($texto) > self::MAX_CARACTERES_ESTADO
            ? mb_substr($texto, 0, self::MAX_CARACTERES_ESTADO)
            : $texto;
    }

    /**
     * @param  array<string, string>  $estado
     * @param  array<string, mixed>  $pergunta
     * @return array<string, mixed>|null
     */
    private function perguntar(array $estado, array $pergunta): ?array
    {
        $corpo = [
            'model' => config('services.jev.model'),
            'state' => $estado,
            'questions' => [$pergunta],
        ];

        try {
            $resposta = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.jev.api_key'),
                'Content-Type' => 'application/json',
            ])
                ->timeout((int) config('services.jev.timeout', 60))
                ->post(
                    rtrim((string) config('services.jev.url'), '/') . '/' . ltrim((string) config('services.jev.endpoint'), '/'),
                    $corpo
                );
        } catch (\Throwable $excepcao) {
            Log::error('JevClient: falha ao contactar o JEV.', ['message' => $excepcao->getMessage()]);

            return null;
        }

        if (!$resposta->successful()) {
            Log::error('JevClient: o JEV rejeitou o pedido.', [
                'status' => $resposta->status(),
                'body' => $resposta->body(),
            ]);

            return null;
        }

        return $resposta->json();
    }

    /**
     * Tira a probabilidade da resposta.
     *
     * Procura-a nos sítios onde é plausível que venha, porque a forma exacta da
     * resposta não pôde ser confirmada na documentação. Quando não a encontra,
     * regista o corpo inteiro: é essa linha do log que diz como corrigir isto.
     *
     * @param  array<string, mixed>  $resposta
     */
    private function probabilidade(array $resposta): ?float
    {
        $caminhos = [
            ['answers', 'compatibilidade', 'probability'],
            ['answers', 'compatibilidade', 'value'],
            ['answers', 0, 'probability'],
            ['answers', 0, 'value'],
            ['results', 0, 'probability'],
            ['results', 0, 'value'],
            ['compatibilidade', 'probability'],
            ['probability'],
        ];

        foreach ($caminhos as $caminho) {
            $valor = $resposta;

            foreach ($caminho as $chave) {
                if (!is_array($valor) || !array_key_exists($chave, $valor)) {
                    $valor = null;

                    break;
                }

                $valor = $valor[$chave];
            }

            if (is_numeric($valor)) {
                return $this->normalizar((float) $valor);
            }
        }

        Log::error('JevClient: não encontrei a probabilidade na resposta do JEV.', [
            'body' => $resposta,
        ]);

        return null;
    }

    /**
     * O noul devolve 0..1, e é assim que o resto da aplicação o trata.
     *
     * Só se divide por 100 o que não pode ser outra coisa senão uma
     * percentagem. Entre 1 e 1.5 fica-se pelo corte: um 1.4 é muito mais
     * provavelmente uma probabilidade a sair fora do intervalo do que 1,4% de
     * compatibilidade, e dividi-lo daria 1% a um candidato que talvez seja bom.
     */
    private function normalizar(float $valor): float
    {
        if ($valor > 1.5 && $valor <= 100.0) {
            $valor = $valor / 100;
        }

        if ($valor < 0.0 || $valor > 1.0) {
            Log::warning('JevClient: probabilidade fora do intervalo; cortada.', ['valor' => $valor]);
        }

        return max(0.0, min(1.0, $valor));
    }
}

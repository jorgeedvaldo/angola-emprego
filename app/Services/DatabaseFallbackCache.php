<?php

namespace App\Services;

use App\Support\FalhaDeLigacao;
use Closure;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Cache com cópia de emergência.
 *
 * Guarda cada resultado duas vezes:
 *
 *   - a **cópia normal**, curta, que é a que se serve no dia a dia;
 *   - a **cópia de emergência**, longa, que só se toca quando o MySQL não
 *     responde.
 *
 * A diferença entre as duas é o ponto todo. A normal expira depressa para o
 * site estar actualizado; a de emergência fica lá dias, e é por isso que uma
 * página continua a abrir quando a base de dados recusa ligações.
 *
 * Só serve para **dados públicos**. Nada que dependa de quem está autenticado
 * passa por aqui — a cópia de emergência é partilhada por todos os visitantes.
 */
class DatabaseFallbackCache
{
    private const PREFIXO = 'fb:';
    private const EMERGENCIA = 'fb:sos:';

    private Repository $cache;

    public function __construct(private CircuitoBaseDeDados $circuito, ?Repository $cache = null)
    {
        $this->cache = $cache ?: Cache::store(config('resiliencia.loja'));
    }

    /**
     * @param  string   $chave      identifica estes dados
     * @param  int      $segundos   quanto dura a cópia normal
     * @param  Closure  $consulta   como ir buscar os dados à base de dados
     * @param  int|null $emergencia quanto dura a cópia de emergência
     *
     * @throws \App\Exceptions\BaseDeDadosIndisponivel quando não há ligação nem cópia
     */
    public function lembrar(string $chave, int $segundos, Closure $consulta, ?int $emergencia = null)
    {
        $normal = self::PREFIXO . $chave;

        $guardado = $this->cache->get($normal);

        if ($guardado !== null) {
            return $this->desembrulhar($guardado);
        }

        // O disjuntor está aberto: nem se tenta: cada tentativa ocupa uma das
        // ligações que faltam para o site voltar.
        if ($this->circuito->aberto()) {
            return $this->emergencia($chave, null);
        }

        try {
            $dados = $consulta();
        } catch (Throwable $erro) {
            if (!FalhaDeLigacao::e($erro)) {
                throw $erro;
            }

            $this->circuito->falha();

            Log::warning('[resiliencia] A consulta ":chave" falhou por falta de ligação.', [
                'chave' => $chave,
                'erro' => $erro->getMessage(),
            ]);

            return $this->emergencia($chave, $erro);
        }

        $this->circuito->sucesso();
        $this->guardar($chave, $segundos, $emergencia, $dados);

        return $dados;
    }

    /** Deita fora as duas cópias — para quando o conteúdo muda. */
    public function esquecer(string $chave): void
    {
        $this->cache->forget(self::PREFIXO . $chave);
        $this->cache->forget(self::EMERGENCIA . $chave);
    }

    /** Deita fora só a cópia normal, deixando a de emergência de pé. */
    public function esquecerANormal(string $chave): void
    {
        $this->cache->forget(self::PREFIXO . $chave);
    }

    public function temCopiaDeEmergencia(string $chave): bool
    {
        return $this->cache->get(self::EMERGENCIA . $chave) !== null;
    }

    private function guardar(string $chave, int $segundos, ?int $emergencia, $dados): void
    {
        $embrulho = $this->embrulhar($dados);

        $this->cache->put(self::PREFIXO . $chave, $embrulho, $segundos);

        $this->cache->put(
            self::EMERGENCIA . $chave,
            $embrulho,
            $emergencia ?? (int) config('resiliencia.emergencia')
        );
    }

    private function emergencia(string $chave, ?Throwable $erro)
    {
        $guardado = $this->cache->get(self::EMERGENCIA . $chave);

        if ($guardado !== null) {
            Log::info('[resiliencia] A servir ":chave" da cópia de emergência.', ['chave' => $chave]);

            return $this->desembrulhar($guardado);
        }

        Log::error('[resiliencia] Sem ligação e sem cópia de ":chave".', ['chave' => $chave]);

        throw FalhaDeLigacao::converter($erro ?: new \RuntimeException('Sem ligação à base de dados.'));
    }

    /**
     * O null é um resultado legítimo de uma consulta, mas na cache é
     * indistinguível de "não está cá". Por isso guarda-se sempre embrulhado.
     */
    private function embrulhar($dados): array
    {
        return ['v' => $dados];
    }

    private function desembrulhar($guardado)
    {
        return is_array($guardado) && array_key_exists('v', $guardado) ? $guardado['v'] : $guardado;
    }
}

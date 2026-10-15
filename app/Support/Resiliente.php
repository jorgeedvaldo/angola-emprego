<?php

namespace App\Support;

use App\Services\DatabaseFallbackCache;
use Closure;

/**
 * Atalho para a cache com cópia de emergência.
 *
 * Existe para que o sítio onde se faz a consulta fique a ler-se como antes —
 * `Resiliente::lembrar('latest_jobs_50', ...)` em vez de um `app(...)` por
 * cima de cada uma.
 */
class Resiliente
{
    public static function lembrar(string $chave, int $segundos, Closure $consulta, ?int $emergencia = null)
    {
        return app(DatabaseFallbackCache::class)->lembrar($chave, $segundos, $consulta, $emergencia);
    }

    public static function esquecer(string ...$chaves): void
    {
        $cache = app(DatabaseFallbackCache::class);

        foreach ($chaves as $chave) {
            // Só a cópia normal: a de emergência é a rede que segura o site
            // quando o MySQL cai, e não se deita fora por o conteúdo ter
            // mudado. Ela própria é reescrita na próxima consulta que corra.
            $cache->esquecerANormal($chave);
        }
    }
}

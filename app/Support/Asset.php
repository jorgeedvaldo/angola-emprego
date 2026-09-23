<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Endereços de ficheiros estáticos com a versão colada ao fim.
 *
 * Sem isto, o browser guarda o JavaScript em cache e continua a usá-lo depois
 * de o servidor ter mudado — e um JavaScript antigo a falar com um servidor
 * novo é um erro difícil de diagnosticar, porque o servidor está certo, o
 * ficheiro em disco está certo, e mesmo assim a página não funciona.
 *
 * Foi exactamente o que aconteceu quando o analisador de CVs passou a poder
 * usar o JEV: o servidor passou a esperar a descrição da vaga em cada pedido, o
 * JavaScript novo passou a enviá-la, mas os browsers continuaram com o antigo,
 * que não a envia. Todos os CVs falhavam com "A análise da vaga expirou".
 *
 * A versão é a data de alteração do ficheiro: muda sozinha a cada publicação
 * que toque no ficheiro, e não muda quando não é preciso.
 */
class Asset
{
    public static function versionado(string $caminho): string
    {
        $url = asset($caminho);
        $versao = self::versao($caminho);

        return $versao === null ? $url : $url . '?v=' . $versao;
    }

    /**
     * A data de alteração do ficheiro, em cache para não ir ao disco a cada
     * página. A chave inclui o caminho, e a cache é limpa pelo
     * `artisan optimize:clear` que a publicação já corre.
     */
    private static function versao(string $caminho): ?string
    {
        return Cache::remember('asset-versao:' . $caminho, 3600, function () use ($caminho) {
            $ficheiro = public_path($caminho);

            // Sem o ficheiro não há versão; o endereço sai como sempre saiu, em
            // vez de rebentar a página por causa de um <script>.
            return is_file($ficheiro) ? (string) filemtime($ficheiro) : null;
        });
    }
}

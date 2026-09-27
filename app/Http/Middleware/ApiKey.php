<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Exige uma chave para chegar às rotas que alteram ou destroem dados.
 *
 * A API deste portal nasceu aberta: o /api/job/create aceita vagas de quem
 * quer que seja. Publicar uma vaga a mais é chato e desfaz-se; apagar vagas
 * não se desfaz, e uma rota de apagar aberta ao mundo era um botão para
 * qualquer pessoa esvaziar o site. Daí esta porta.
 *
 * A chave viaja no cabeçalho X-API-Key e compara-se com a do .env (API_KEY).
 * Sem chave configurada no servidor, a rota fica fechada a toda a gente — é
 * preferível a ficar aberta por esquecimento.
 */
class ApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $esperada = (string) config('services.api.key');
        $recebida = (string) ($request->header('X-API-Key') ?? $request->bearerToken());

        if ($esperada === '') {
            return response()->json([
                'message' => 'Esta rota exige uma chave de API, e o servidor não tem nenhuma configurada.',
            ], 503);
        }

        // hash_equals compara sempre no mesmo tempo: uma comparação normal
        // demora mais quanto mais caracteres acertarem, e isso chega para
        // adivinhar a chave letra a letra.
        if ($recebida === '' || !hash_equals($esperada, $recebida)) {
            return response()->json([
                'message' => 'Chave de API em falta ou inválida. Envie-a no cabeçalho X-API-Key.',
            ], 401);
        }

        return $next($request);
    }
}

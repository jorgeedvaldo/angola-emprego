<?php

namespace App\Exceptions;

use App\Services\CircuitoBaseDeDados;
use App\Support\FalhaDeLigacao;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Quando a base de dados não responde, o visitante vê uma página calma em
     * vez de um 500.
     *
     * Só para isso. Um erro de programação continua a aparecer como erro — se
     * tudo o que rebenta virasse "serviço indisponível", uma avaria a sério
     * podia ficar meses sem ninguém dar por ela.
     */
    public function render($request, Throwable $e)
    {
        if (!FalhaDeLigacao::e($e)) {
            return parent::render($request, $e);
        }

        // Abre o disjuntor: a falha chegou aqui sem passar pelo serviço de
        // cache, e as requisições seguintes não devem voltar a tentar.
        app(CircuitoBaseDeDados::class)->falha();

        Log::error('[resiliencia] Pedido servido sem base de dados: :metodo :caminho', [
            'metodo' => $request->method(),
            'caminho' => $request->path(),
            'erro' => $e->getMessage(),
        ]);

        return $this->respostaDeIndisponibilidade($request);
    }

    /**
     * Um 503 com Retry-After, que é o que diz aos motores de busca para
     * voltarem mais tarde sem tirarem a página do índice.
     */
    private function respostaDeIndisponibilidade(Request $request): Response
    {
        $segundos = (int) config('resiliencia.descanso', 30);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Serviço temporariamente indisponível. Tente novamente dentro de alguns instantes.',
            ], 503)->header('Retry-After', $segundos);
        }

        $pagina = public_path('database-unavailable.html');

        return response(
            is_file($pagina) ? file_get_contents($pagina) : 'Serviço temporariamente indisponível.',
            503,
            [
                'Content-Type' => 'text/html; charset=UTF-8',
                'Retry-After' => $segundos,
                'Cache-Control' => 'no-store, max-age=0',
            ]
        );
    }
}

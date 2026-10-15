<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response as RespostaBase;

/**
 * Guarda a página pública já desenhada e serve-a sem voltar às consultas.
 *
 * Isto é o andar de cima da cache de dados: aquela evita as consultas, esta
 * evita também desenhar a página outra vez. Em cima de um alojamento com 30
 * ligações, cada requisição que não chega ao MySQL é uma ligação livre.
 *
 * O que **nunca** entra na cache:
 *
 *   - seja o que for que não seja um GET com resposta 200 em HTML;
 *   - páginas de quem tem sessão iniciada — a cópia é partilhada por todos;
 *   - páginas com dados em memória da sessão (erros de formulário, avisos);
 *   - endereços com parâmetros que não o `page` (pesquisas e filtros);
 *   - rotas que não estejam na lista do config/resiliencia.php.
 *
 * O token CSRF é trocado por uma marca antes de guardar e reposto no momento
 * de servir. Sem isso, o primeiro visitante deixava o token dele na cópia e
 * todos os outros recebiam-no — os formulários ou rebentavam com 419, ou
 * passavam a partilhar um token, que é pior.
 */
class CacheDePaginasPublicas
{
    private const MARCA_CSRF = '@@csrf-token@@';
    private const PREFIXO = 'html:';
    private const VERSAO = 'html:versao';
    private const CABECALHOS = ['Content-Type', 'Content-Language'];

    private Repository $cache;

    public function __construct()
    {
        $this->cache = Cache::store(config('resiliencia.loja'));
    }

    public function handle(Request $request, Closure $next)
    {
        if (!$this->podeGuardar($request)) {
            return $next($request);
        }

        $chave = $this->chave($request);
        $guardado = $this->cache->get(self::PREFIXO . $chave);

        if (is_array($guardado)) {
            return $this->servir($guardado);
        }

        $resposta = $next($request);

        if ($this->respostaServe($request, $resposta)) {
            $this->guardar($chave, $resposta);
        }

        return $resposta;
    }

    // --------------------------------------------------------------- decidir

    private function podeGuardar(Request $request): bool
    {
        if (!config('resiliencia.html.activa') || !$request->isMethod('GET')) {
            return false;
        }

        // Um parâmetro qualquer que não seja o `page` faz disto uma pesquisa
        // ou um filtro: são infinitos e não vale a pena guardá-los.
        if (array_diff_key($request->query(), array_flip(['page'])) !== []) {
            return false;
        }

        if ($this->temSessaoIniciada($request)) {
            return false;
        }

        return $request->is(...config('resiliencia.html.caminhos'));
    }

    /**
     * Saber se há sessão iniciada sem ir à base de dados.
     *
     * Um Auth::check() carregava o utilizador — mais uma consulta em cada
     * página, que é exactamente o que se quer evitar. A chave da sessão e o
     * cookie de "lembrar-me" dizem o mesmo sem tocar no MySQL.
     */
    private function temSessaoIniciada(Request $request): bool
    {
        $guarda = Auth::guard('web');

        // hasUser() não vai à base de dados: diz só se já há utilizador
        // resolvido neste pedido.
        if ($guarda->hasUser()) {
            return true;
        }

        if ($request->hasSession() && $request->session()->has($guarda->getName())) {
            return true;
        }

        return $request->cookies->has($guarda->getRecallerName());
    }

    private function respostaServe(Request $request, RespostaBase $resposta): bool
    {
        if ($resposta->getStatusCode() !== 200 || !$resposta instanceof Response) {
            return false;
        }

        if (!str_contains((string) $resposta->headers->get('Content-Type'), 'text/html')) {
            return false;
        }

        // Erros de formulário e avisos de uma acção anterior são daquele
        // visitante e vivem na sessão; guardá-los era mostrá-los a estranhos.
        if ($request->hasSession() && ($request->session()->has('errors') || $this->temAvisos($request))) {
            return false;
        }

        $conteudo = $resposta->getContent();

        return $conteudo !== false
            && $conteudo !== ''
            && strlen($conteudo) <= (int) config('resiliencia.html.tamanho_maximo');
    }

    private function temAvisos(Request $request): bool
    {
        foreach (['status', 'success', 'error', 'message', 'warning'] as $chave) {
            if ($request->session()->has($chave)) {
                return true;
            }
        }

        return false;
    }

    // --------------------------------------------------------------- guardar

    private function guardar(string $chave, Response $resposta): void
    {
        $cabecalhos = [];

        foreach (self::CABECALHOS as $nome) {
            if ($resposta->headers->has($nome)) {
                $cabecalhos[$nome] = $resposta->headers->get($nome);
            }
        }

        $this->cache->put(
            self::PREFIXO . $chave,
            [
                'html' => str_replace(csrf_token(), self::MARCA_CSRF, $resposta->getContent()),
                'cabecalhos' => $cabecalhos,
                'em' => now()->toIso8601String(),
            ],
            (int) config('resiliencia.html.duracao')
        );
    }

    private function servir(array $guardado): Response
    {
        $resposta = new Response(
            str_replace(self::MARCA_CSRF, csrf_token(), $guardado['html']),
            200,
            $guardado['cabecalhos'] ?? []
        );

        return $resposta->header('X-Cache-Angola', 'hit')
            ->header('X-Cache-Angola-Em', $guardado['em'] ?? '');
    }

    /**
     * O idioma entra na chave: o site fala português e inglês na mesma morada,
     * e sem isto um visitante inglês deixava a página dele a toda a gente.
     */
    private function chave(Request $request): string
    {
        return self::versao() . ':' . sha1(implode('|', [
            $request->getHost(),
            $request->path(),
            (string) $request->query('page', ''),
            app()->getLocale(),
        ]));
    }

    /**
     * A versão entra na chave para se poder deitar a cache toda fora de uma
     * vez quando o conteúdo muda. A loja de ficheiros não sabe apagar por
     * prefixo, e apagar tudo levava à frente a cache de dados, que é o que
     * segura o site quando o MySQL cai.
     */
    public static function versao(): int
    {
        return (int) Cache::store(config('resiliencia.loja'))->get(self::VERSAO, 1);
    }

    /** Invalida a cache de HTML toda. As cópias antigas expiram sozinhas. */
    public static function invalidar(): void
    {
        $cache = Cache::store(config('resiliencia.loja'));

        $cache->forever(self::VERSAO, self::versao() + 1);
    }
}

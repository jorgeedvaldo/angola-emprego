<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

/**
 * Escolhe o idioma de cada pedido.
 *
 * A escolha do visitante fica na sessão, por ser a única coisa que sobrevive
 * entre páginas sem exigir conta nem sujar os URLs. Quem nunca escolheu vê o
 * site em português.
 *
 * Deliberadamente NÃO se olha para o Accept-Language do browser. Seria a
 * escolha óbvia, mas o público deste site é angolano e é muito comum o telemóvel
 * vir configurado em inglês — detectar o browser passaria a mostrar o site em
 * inglês a gente que o quer em português, e que talvez nem repare no selector
 * para o corrigir. Português por omissão, inglês a pedido, é o mais previsível
 * para quem cá vem.
 */
class SetLocale
{
    public const SESSION_KEY = 'locale';

    public function handle(Request $request, Closure $next)
    {
        App::setLocale($this->escolher($request));

        return $next($request);
    }

    private function escolher(Request $request): string
    {
        $daSessao = $request->session()->get(self::SESSION_KEY);

        return self::suportado($daSessao) ? $daSessao : self::porOmissao();
    }

    /**
     * O idioma do site. Lido do config na primeira vez e guardado, porque
     * App::setLocale() escreve por cima de config('app.locale') — sem isto, o
     * "por omissão" passaria a ser o último idioma escolhido por alguém.
     */
    private static function porOmissao(): string
    {
        static $omissao = null;

        return $omissao ??= config('app.locale');
    }

    /**
     * @return array<string, array{nativo: string, bandeira: string}>
     */
    public static function suportados(): array
    {
        return config('locales.suportados', []);
    }

    public static function suportado(?string $locale): bool
    {
        return $locale !== null && array_key_exists($locale, self::suportados());
    }
}

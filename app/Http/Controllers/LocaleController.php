<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Guarda o idioma escolhido e devolve o visitante à página onde estava.
     */
    public function switch(Request $request, string $locale)
    {
        abort_unless(SetLocale::suportado($locale), 404);

        $request->session()->put(SetLocale::SESSION_KEY, $locale);

        // back() usa o cabeçalho Referer, que pode vir de fora do site; o
        // fallback para a página inicial evita servir de trampolim para outro
        // domínio a partir de um link nosso.
        return redirect()->back(302, [], route('home'));
    }
}

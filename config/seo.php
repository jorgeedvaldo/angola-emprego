<?php

return [

    /*
    |--------------------------------------------------------------------------
    | O nome do site
    |--------------------------------------------------------------------------
    |
    | É o que o Google mostra por cima do resultado, no lugar do domínio, e o
    | que as redes sociais mostram por cima da partilha. O Google só o aceita
    | se for um nome — um título com slogan ("Angola Emprego - Notícias e
    | Empregos") é recusado e ele volta a escrever "angolaemprego.com".
    |
    | Não vem do APP_NAME de propósito: o APP_NAME também assina os emails e
    | acaba quase sempre com o slogan colado.
    |
    */

    'nome' => env('SEO_SITE_NAME', 'Angola Emprego'),

    /*
    | O nome por extenso, se houver. Vai para o alternateName do schema, que é
    | onde o Google aceita a forma comprida sem a confundir com o nome.
    */

    'nome_alternativo' => env('SEO_SITE_ALT_NAME', 'Angola Emprego - Notícias e Empregos'),

];

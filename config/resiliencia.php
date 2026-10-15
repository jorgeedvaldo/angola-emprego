<?php

return [

    /*
    |--------------------------------------------------------------------------
    | A loja de cache
    |--------------------------------------------------------------------------
    |
    | Tem de ser uma loja que NÃO dependa do MySQL — se dependesse, caía com
    | ele e não servia para nada. Segue a loja por omissão da aplicação, com
    | uma excepção: se alguém puser CACHE_DRIVER=database, esta volta sozinha
    | ao sistema de ficheiros, porque uma rede de segurança pendurada na coisa
    | que se partiu não é rede nenhuma.
    |
    */

    'loja' => env('RESILIENCIA_CACHE') ?: (
        in_array(env('CACHE_DRIVER', 'file'), ['database', 'dynamodb'], true)
            ? 'file'
            : env('CACHE_DRIVER', 'file')
    ),

    /*
    |--------------------------------------------------------------------------
    | Duração das cópias (em segundos)
    |--------------------------------------------------------------------------
    |
    | A normal é curta para o site estar actualizado. A de emergência é longa,
    | e é a que mantém as páginas de pé quando o MySQL recusa ligações.
    |
    */

    'normal' => (int) env('RESILIENCIA_NORMAL', 1800),          // 30 minutos
    'emergencia' => (int) env('RESILIENCIA_EMERGENCIA', 604800), // 7 dias
    'sitemap' => (int) env('RESILIENCIA_SITEMAP', 21600),      // 6 horas

    /*
    |--------------------------------------------------------------------------
    | O disjuntor
    |--------------------------------------------------------------------------
    |
    | Ao fim de `falhas_para_abrir` falhas seguidas de ligação, deixa-se de
    | tentar durante `descanso` segundos. Passado isso, uma requisição — e só
    | uma, durante `espera_da_sonda` segundos — vai ver se já há ligação.
    |
    */

    'falhas_para_abrir' => (int) env('RESILIENCIA_FALHAS', 3),
    'janela_de_falhas' => (int) env('RESILIENCIA_JANELA', 60),
    'descanso' => (int) env('RESILIENCIA_DESCANSO', 30),
    'espera_da_sonda' => (int) env('RESILIENCIA_SONDA', 5),

    /*
    |--------------------------------------------------------------------------
    | Cache de HTML das páginas públicas
    |--------------------------------------------------------------------------
    |
    | Guarda a página já desenhada e serve-a sem voltar a correr as consultas.
    | Só para visitantes sem sessão iniciada e só nas rotas da lista.
    |
    */

    'html' => [
        'activa' => (bool) env('RESILIENCIA_HTML', true),
        'duracao' => (int) env('RESILIENCIA_HTML_DURACAO', 600),     // 10 minutos
        'emergencia' => (int) env('RESILIENCIA_HTML_SOS', 86400),    // 1 dia
        'tamanho_maximo' => 512 * 1024,

        /*
        | As rotas que podem ir para a cache de HTML. Tudo o que não estiver
        | aqui passa ao lado — é uma lista de autorizações, não de proibições,
        | para que uma rota nova nunca entre na cache por distracção.
        */
        'caminhos' => [
            '/',
            'vagas',
            'vagas/*',
            'noticias',
            'noticias/*',
            'cursos',
            'cursos/*',
            'empresas',
            'empresas-e-recrutadores',
            'sobre',
            'analise-de-cv',
        ],
    ],

];

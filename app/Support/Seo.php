<?php

namespace App\Support;

/**
 * O cabeçalho de SEO da página, num sítio só.
 *
 * É o que um plugin como o Yoast faz num WordPress: recolhe o título, a
 * descrição, o endereço canónico e a imagem da página, e escreve com isso as
 * etiquetas que os motores de busca e as redes sociais lêem.
 *
 * As vistas continuam a declarar o que sabem por @section; esta classe trata do
 * resto — o que se pode deduzir sozinho e o que estava a faltar.
 */
class Seo
{
    /** O que se diz aos motores de busca numa página que queremos indexada. */
    public const ROBOTS_INDEXAVEL = 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1';

    /**
     * E numa que não queremos: "não guardes esta página, mas segue os links
     * que estão nela". É para páginas privadas e para resultados de pesquisa,
     * que são infinitos e não têm conteúdo próprio.
     */
    public const ROBOTS_ESCONDIDO = 'noindex, follow, max-image-preview:large';

    /** Parâmetros que não tornam a página um duplicado. */
    private const PARAMETROS_INOFENSIVOS = ['page'];

    /** Palavras por minuto, para o tempo de leitura. */
    private const VELOCIDADE_DE_LEITURA = 200;

    /** @var array<string, array{0:int,1:int,2:string}|null> */
    private static array $medidas = [];

    /**
     * O endereço canónico. Sem um, páginas iguais com endereços diferentes
     * (com ?fbclid=, com ?gclid=, com e sem barra) contam como duplicados e
     * dividem entre si a força que deviam ter junta.
     */
    public static function canonica(?string $declarada): string
    {
        return trim((string) $declarada) ?: url()->current();
    }

    /**
     * Uma página com parâmetros na barra de endereço é quase sempre uma
     * pesquisa ou um filtro: tem o conteúdo de outra e não deve ser indexada.
     * O `page` é a excepção — as páginas 2, 3, 4 de uma listagem são conteúdo
     * a sério.
     */
    public static function robots(?string $declarado): string
    {
        if (trim((string) $declarado) !== '') {
            return trim($declarado);
        }

        $parametros = array_diff_key(request()->query(), array_flip(self::PARAMETROS_INOFENSIVOS));

        return $parametros === [] ? self::ROBOTS_INDEXAVEL : self::ROBOTS_ESCONDIDO;
    }

    /**
     * Mede a imagem de partilha. O Facebook e o LinkedIn acreditam no que lhes
     * dizemos: uma medida errada faz a imagem sair cortada ou não sair de todo.
     *
     * @return array{url:string, largura:?int, altura:?int, tipo:?string}
     */
    public static function imagem(?string $url): array
    {
        $url = trim((string) $url) ?: asset('assets/img/og-default.png');
        $medida = self::medir($url);

        return [
            'url' => $url,
            'largura' => $medida[0] ?? null,
            'altura' => $medida[1] ?? null,
            'tipo' => $medida[2] ?? null,
        ];
    }

    /** Minutos de leitura, como o "tempo estimado de leitura" do Yoast. */
    public static function tempoDeLeitura(?string $html): int
    {
        $palavras = str_word_count(strip_tags((string) $html), 0, 'áàâãéêíóôõúçÁÀÂÃÉÊÍÓÔÕÚÇ');

        return max(1, (int) ceil($palavras / self::VELOCIDADE_DE_LEITURA));
    }

    /**
     * O grafo de dados estruturados do site.
     *
     * São três coisas ligadas por @id, e é a ligação que importa: o Google lê
     * "esta página faz parte deste site, que é publicado por esta organização".
     * Soltas, como estavam, são três fichas sem relação nenhuma entre si.
     *
     * @param array{titulo:string, descricao:string, canonica:string, imagem:array} $pagina
     */
    public static function grafo(array $pagina): array
    {
        $site = rtrim(url('/'), '/') . '/';
        $idioma = self::idioma();
        $nome = config('app.name');

        $imagem = [
            '@type' => 'ImageObject',
            '@id' => $pagina['canonica'] . '#imagem',
            'url' => $pagina['imagem']['url'],
            'contentUrl' => $pagina['imagem']['url'],
        ];

        if ($pagina['imagem']['largura']) {
            $imagem['width'] = $pagina['imagem']['largura'];
            $imagem['height'] = $pagina['imagem']['altura'];
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'WebSite',
                    '@id' => $site . '#website',
                    'url' => $site,
                    'name' => $nome,
                    'description' => __('site.seo.site_descricao'),
                    'inLanguage' => $idioma,
                    'publisher' => ['@id' => $site . '#organizacao'],

                    // A caixa de pesquisa que o Google pode mostrar por baixo
                    // do site nos resultados.
                    'potentialAction' => [[
                        '@type' => 'SearchAction',
                        'target' => [
                            '@type' => 'EntryPoint',
                            'urlTemplate' => url('/vagas') . '?q={search_term_string}',
                        ],
                        'query-input' => 'required name=search_term_string',
                    ]],
                ],
                [
                    '@type' => 'Organization',
                    '@id' => $site . '#organizacao',
                    'name' => $nome,
                    'url' => $site,
                    'description' => __('site.seo.organizacao_descricao'),
                    'email' => 'geral@angolaemprego.com',
                    'telephone' => '+244-951-014-936',
                    // O logo-og.png tem o "ANGOLA" a branco, para as capas
                    // escuras; num resultado do Google, que é branco, metade
                    // dele desaparecia. Este é o mesmo logótipo em versão
                    // legível sobre branco.
                    'logo' => [
                        '@type' => 'ImageObject',
                        '@id' => $site . '#logotipo',
                        'url' => asset('assets/img/logo-schema.png'),
                        'contentUrl' => asset('assets/img/logo-schema.png'),
                        'width' => 1138,
                        'height' => 406,
                        'caption' => $nome,
                    ],
                    'image' => ['@id' => $site . '#logotipo'],
                    'address' => [
                        '@type' => 'PostalAddress',
                        'addressLocality' => 'Luanda',
                        'addressCountry' => 'AO',
                    ],
                    'sameAs' => [
                        'https://www.facebook.com/aoemprego',
                        'https://www.linkedin.com/company/angola-emprego/',
                    ],
                ],
                [
                    // A listagem de vagas é uma CollectionPage, o artigo é uma
                    // WebPage. As duas com o mesmo @id da ficha que a própria
                    // página escreve, para serem a mesma entidade.
                    '@type' => $pagina['tipo'] ?? 'WebPage',
                    '@id' => $pagina['canonica'],
                    'url' => $pagina['canonica'],
                    'name' => $pagina['titulo'],
                    'description' => $pagina['descricao'],
                    'inLanguage' => $idioma,
                    'isPartOf' => ['@id' => $site . '#website'],
                    'about' => ['@id' => $site . '#organizacao'],
                    'primaryImageOfPage' => ['@id' => $pagina['canonica'] . '#imagem'],
                ],
                $imagem,
            ],
        ];
    }

    /** pt-AO para o português; o código do idioma para o resto. */
    public static function idioma(): string
    {
        return app()->getLocale() === 'pt' ? 'pt-AO' : app()->getLocale();
    }

    /** O mesmo, na forma que o Open Graph quer. */
    public static function idiomaOpenGraph(): string
    {
        return str_replace('-', '_', self::idioma());
    }

    /**
     * Mede um ficheiro que esteja no nosso próprio public/. De uma imagem
     * noutro sítio não se sabe nada, e mais vale não dizer nada.
     *
     * @return array{0:int,1:int,2:string}|null
     */
    private static function medir(string $url): ?array
    {
        if (array_key_exists($url, self::$medidas)) {
            return self::$medidas[$url];
        }

        $caminho = public_path(ltrim(parse_url($url, PHP_URL_PATH) ?: '', '/'));
        $medida = null;

        if (is_file($caminho) && ($lido = @getimagesize($caminho))) {
            $medida = [$lido[0], $lido[1], $lido['mime']];
        }

        return self::$medidas[$url] = $medida;
    }

    /** Para os testes, que medem imagens diferentes em cada um. */
    public static function esquecerMedidas(): void
    {
        self::$medidas = [];
    }
}

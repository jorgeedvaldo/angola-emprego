<?php

namespace Tests\Unit;

use App\Http\Controllers\ArticleImageController;
use ReflectionMethod;
use Tests\TestCase;

/**
 * O cartão social de cada vaga e de cada notícia é desenhado em PHP com o GD.
 * Durante muito tempo mostrou um "B" — o logótipo de outro produto, que ficou
 * no código de onde este gerador foi copiado. Estes testes guardam a marca:
 * o ficheiro do logótipo tem de existir, tem de ser desenhado, e as cores têm
 * de ser as de um logótipo pensado para fundo escuro.
 */
class ArticleImageTest extends TestCase
{
    private const LOGO = 'assets/img/logo-og.png';

    public function test_the_logo_file_ships_with_the_site()
    {
        $this->assertFileExists(public_path(self::LOGO));

        [$largura, $altura] = getimagesize(public_path(self::LOGO));

        // O logótipo do menu é largo (568.79 x 203.08 no SVG). Se alguém lá puser
        // um quadrado, é sinal de que voltou a ser o ficheiro errado.
        $this->assertGreaterThan(2.5, $largura / $altura, 'O logótipo devia ter a proporção do original.');
        $this->assertGreaterThanOrEqual(600, $largura, 'Guardamos o logótipo em grande para reduzir sem serrilhar.');
    }

    public function test_the_card_is_the_size_the_meta_tags_promise()
    {
        $ficheiro = $this->desenhar('Vaga para Técnico de Recursos Humanos', 'VAGA');

        [$largura, $altura] = getimagesize($ficheiro);

        $this->assertSame(1200, $largura);
        $this->assertSame(630, $altura);
    }

    /**
     * O "ANGOLA" do logótipo original é quase preto e sobre este azul-escuro não
     * se veria. A variante do cartão tem de o trazer a branco.
     */
    public function test_the_wordmark_is_light_enough_to_read_on_the_dark_card()
    {
        $ficheiro = $this->desenhar('Vaga para Técnico de Recursos Humanos', 'VAGA');
        $imagem = imagecreatefrompng($ficheiro);

        $claros = 0;

        // Faixa onde o logótipo é desenhado: x 70..320, y 70..156.
        for ($y = 70; $y < 156; $y++) {
            for ($x = 70; $x < 320; $x++) {
                $cor = imagecolorat($imagem, $x, $y);
                [$r, $g, $b] = [($cor >> 16) & 255, ($cor >> 8) & 255, $cor & 255];

                if ($r > 200 && $g > 200 && $b > 200) {
                    $claros++;
                }
            }
        }

        imagedestroy($imagem);

        $this->assertGreaterThan(
            500,
            $claros,
            'Quase não há pixéis claros onde devia estar o logótipo — ou não foi desenhado, ou ficou escuro sobre escuro.'
        );
    }

    /**
     * Se o ficheiro do logótipo desaparecer, o cartão continua a sair — com o
     * nome do site escrito — em vez de rebentar a publicação de uma vaga.
     */
    public function test_a_missing_logo_does_not_break_the_card()
    {
        $original = public_path(self::LOGO);
        $guardado = $original . '.teste';

        rename($original, $guardado);

        try {
            $ficheiro = $this->desenhar('Vaga sem logótipo', 'VAGA');

            $this->assertFileExists($ficheiro);
            $this->assertSame([1200, 630], array_slice(getimagesize($ficheiro), 0, 2));
        } finally {
            rename($guardado, $original);
        }
    }

    private function desenhar(string $titulo, string $etiqueta): string
    {
        $ficheiro = tempnam(sys_get_temp_dir(), 'cartao') . '.png';

        $metodo = new ReflectionMethod(ArticleImageController::class, 'renderCard');
        $metodo->setAccessible(true);
        $metodo->invoke(new ArticleImageController(), $titulo, $etiqueta, $ficheiro);

        return $ficheiro;
    }
}

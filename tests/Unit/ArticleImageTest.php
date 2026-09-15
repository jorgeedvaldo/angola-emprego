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

    /**
     * O cartão do Brasil é verde e amarelo, e o de Angola continua azul. É a
     * primeira coisa que se vê numa partilha, antes de se ler o título.
     */
    public function test_the_brazil_card_is_green_where_the_angolan_one_is_blue()
    {
        $angola = $this->desenhar('Vaga para Motorista', 'VAGA', 'AO', 'Luanda');
        $brasil = $this->desenhar('Vaga para Motorista', 'VAGA', 'BR', 'São Paulo');

        [, , $azulDeAngola] = $this->corMedia($angola);
        [, $verdeDoBrasil, $azulDoBrasil] = $this->corMedia($brasil);

        $this->assertGreaterThan(
            $azulDoBrasil,
            $azulDeAngola,
            'O cartão de Angola devia continuar a ser o mais azul dos dois.'
        );

        $this->assertGreaterThan(
            $azulDoBrasil,
            $verdeDoBrasil,
            'No cartão do Brasil o verde tem de pesar mais do que o azul.'
        );
    }

    public function test_the_brazil_card_uses_the_flag_yellow()
    {
        $brasil = $this->desenhar('Vaga para Motorista', 'VAGA', 'BR', 'São Paulo');

        // O letreiro é uma mancha de amarelo grande; no cartão de Angola não há
        // amarelo nenhum.
        $amarelo = [255, 223, 0];

        $this->assertGreaterThan(
            2000,
            $this->contarPixeis($brasil, $amarelo),
            'Faltam os amarelos do letreiro e da barra lateral.'
        );

        $angola = $this->desenhar('Vaga para Motorista', 'VAGA', 'AO', 'Luanda');

        $this->assertLessThan(
            200,
            $this->contarPixeis($angola, $amarelo),
            'O cartão de Angola não devia ter amarelo.'
        );
    }

    /**
     * Um país sem tema próprio — Espanha, México — fica com o cartão de sempre.
     * Só o Brasil foi pedido diferente.
     */
    public function test_a_country_without_a_theme_of_its_own_keeps_the_usual_card()
    {
        $angola = $this->desenhar('Vaga para Motorista', 'VAGA', 'AO', 'Luanda');
        $espanha = $this->desenhar('Vaga para Motorista', 'VAGA', 'ES', 'Madrid');

        $this->assertFileEquals(
            $angola,
            $espanha,
            'Sem tema próprio, o cartão devia ser igual ao de Angola.'
        );
    }

    /**
     * Sobre o amarelo do letreiro o texto é verde-escuro. Se alguém lhe puser
     * branco, o letreiro deixa de se ler — e ninguém dá por isso até a imagem
     * estar publicada.
     */
    public function test_the_brazil_banner_text_reads_against_its_background()
    {
        $brasil = $this->desenhar('Vaga para Motorista', 'VAGA', 'BR', 'São Paulo');

        // Dentro da caixa do letreiro, no cimo do cartão.
        $regiao = [80, 80, 780, 150];

        $this->assertGreaterThan(
            500,
            $this->contarPixeis($brasil, [6, 53, 26], 30, $regiao),
            'O texto do letreiro devia ser verde-escuro sobre o amarelo.'
        );
    }

    public function test_the_brazil_card_shows_the_job_location()
    {
        $comLocal = $this->desenhar('Vaga para Motorista', 'VAGA', 'BR', 'Belo Horizonte');
        $semLocal = $this->desenhar('Vaga para Motorista', 'VAGA', 'BR', '');

        // A linha da localização ocupa a parte de baixo do cartão; sem ela essa
        // zona fica só com o fundo.
        $rodape = [70, 480, 1130, 600];

        $this->assertGreaterThan(
            $this->contarPixeis($semLocal, [255, 223, 0], 40, $rodape),
            $this->contarPixeis($comLocal, [255, 223, 0], 40, $rodape),
            'A localização devia aparecer por baixo do título.'
        );
    }

    /**
     * Uma morada comprida não pode transbordar do cartão.
     */
    public function test_a_long_location_is_cut_instead_of_overflowing()
    {
        $ficheiro = $this->desenhar(
            'Vaga para Engenheiro',
            'VAGA',
            'BR',
            'Belo Horizonte, Minas Gerais — modelo híbrido com deslocações frequentes a São Paulo e ao Rio'
        );

        $imagem = imagecreatefrompng($ficheiro);
        $amarelo = 0;

        // A margem direita do cartão tem de ficar limpa.
        for ($y = 400; $y < 630; $y++) {
            for ($x = 1140; $x < 1200; $x++) {
                $cor = imagecolorat($imagem, $x, $y);

                if ((($cor >> 16) & 255) > 200 && (($cor >> 8) & 255) > 180 && ($cor & 255) < 90) {
                    $amarelo++;
                }
            }
        }

        imagedestroy($imagem);

        $this->assertSame(0, $amarelo, 'A localização passou para lá da margem do cartão.');
    }

    private function desenhar(
        string $titulo,
        string $etiqueta,
        ?string $pais = null,
        ?string $local = null
    ): string {
        $ficheiro = tempnam(sys_get_temp_dir(), 'cartao') . '.png';

        $metodo = new ReflectionMethod(ArticleImageController::class, 'renderCard');
        $metodo->setAccessible(true);
        $metodo->invoke(new ArticleImageController(), $titulo, $etiqueta, $ficheiro, $pais, $local);

        return $ficheiro;
    }

    /**
     * Conta quantos pixéis da imagem se parecem com a cor dada, dentro de uma
     * tolerância — os gradientes e o anti-aliasing fazem com que quase nenhum
     * pixel seja exactamente a cor pedida.
     *
     * @param  array{0: int, 1: int, 2: int}  $alvo
     */
    private function contarPixeis(string $ficheiro, array $alvo, int $tolerancia = 40, array $regiao = null): int
    {
        $imagem = imagecreatefrompng($ficheiro);
        [$x1, $y1, $x2, $y2] = $regiao ?? [0, 0, imagesx($imagem) - 1, imagesy($imagem) - 1];

        $contagem = 0;

        for ($y = $y1; $y <= $y2; $y += 2) {
            for ($x = $x1; $x <= $x2; $x += 2) {
                $cor = imagecolorat($imagem, $x, $y);
                $r = ($cor >> 16) & 255;
                $g = ($cor >> 8) & 255;
                $b = $cor & 255;

                if (abs($r - $alvo[0]) <= $tolerancia
                    && abs($g - $alvo[1]) <= $tolerancia
                    && abs($b - $alvo[2]) <= $tolerancia) {
                    $contagem++;
                }
            }
        }

        imagedestroy($imagem);

        return $contagem;
    }

    /** Média de cada canal na imagem, para saber que cor domina o cartão. */
    private function corMedia(string $ficheiro): array
    {
        $imagem = imagecreatefrompng($ficheiro);
        $largura = imagesx($imagem);
        $altura = imagesy($imagem);
        $soma = [0, 0, 0];
        $n = 0;

        for ($y = 0; $y < $altura; $y += 3) {
            for ($x = 0; $x < $largura; $x += 3) {
                $cor = imagecolorat($imagem, $x, $y);
                $soma[0] += ($cor >> 16) & 255;
                $soma[1] += ($cor >> 8) & 255;
                $soma[2] += $cor & 255;
                $n++;
            }
        }

        imagedestroy($imagem);

        return [$soma[0] / $n, $soma[1] / $n, $soma[2] / $n];
    }
}

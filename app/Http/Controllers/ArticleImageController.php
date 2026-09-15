<?php

namespace App\Http\Controllers;

use App\Support\CoverTheme;
use Illuminate\Support\Str;

/**
 * Desenha o cartão que representa cada vaga e cada notícia nas redes sociais.
 *
 * É tudo feito em PHP com o GD: sem binários do sistema, sem browser e sem
 * serviços externos, que é o que o alojamento partilhado permite.
 *
 * As cores e a forma do cartão vêm do CoverTheme, escolhido pelo país da vaga:
 * as de Angola levam o azul de sempre com o logótipo do site, as do Brasil levam
 * verde e amarelo com um letreiro a dizer de que país é a vaga. Quem vê a
 * imagem na rede social percebe de onde é antes de ler o título.
 *
 * O logótipo entra como PNG (assets/img/logo-og.png) porque o GD não lê SVG.
 * É o mesmo logótipo do menu, só com o "ANGOLA" a branco em vez de preto — em
 * cima daquele azul-escuro o preto não se via. Para o refazer a partir do
 * assets/img/logo.svg: trocar fill="#070707" por branco e fill="#2677b6" pelo
 * azul claro, e exportar em 1138x406 com fundo transparente.
 */
class ArticleImageController extends Controller
{
    private const WIDTH = 1200;
    private const HEIGHT = 630;

    /** Altura a que o logótipo é desenhado; a largura sai da proporção do ficheiro. */
    private const LOGO_HEIGHT = 86;

    /** Margem lateral e altura a que começa o cabeçalho. */
    private const PAD_X = 70;
    private const TOP_Y = 70;

    /**
     * Desenha o cartão de um título e guarda-o.
     *
     * @param  string|null  $pais   código ISO do país da vaga, que escolhe as cores
     * @param  string|null  $local  localização da vaga, mostrada nos temas que a pedem
     * @return string  caminho relativo a storage/app/public
     */
    public function generate(
        string $title,
        string $directory = 'images/posts',
        string $badge = '',
        ?string $pais = null,
        ?string $local = null
    ): string {
        $dir = storage_path('app/public/' . $directory);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $relative = $directory . '/' . (string) Str::uuid() . '.png';
        $this->renderCard($title, $badge, storage_path('app/public/' . $relative), $pais, $local);

        return $relative;
    }

    /** Bundled TTF font used to render the card text. */
    private function font(): string
    {
        $bold = public_path('fonts/DejaVuSans-Bold.ttf');

        return is_file($bold) ? $bold : public_path('fonts/DejaVuSans.ttf');
    }

    /** Wrap text to a max pixel width using the actual font metrics. */
    private function wrapText(string $text, string $font, int $size, int $maxWidth): array
    {
        $words = preg_split('/\s+/u', trim($text)) ?: [];
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current . ' ' . $word;
            $box = imagettfbbox($size, 0, $font, $candidate);
            $width = abs($box[2] - $box[0]);
            if ($width > $maxWidth && $current !== '') {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $candidate;
            }
        }
        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines ?: [$text];
    }

    private function hex(string $hex): array
    {
        return [
            hexdec(substr($hex, 1, 2)),
            hexdec(substr($hex, 3, 2)),
            hexdec(substr($hex, 5, 2)),
        ];
    }

    /** Aloca uma cor a partir do hexadecimal do tema. */
    private function cor($im, string $hex): int
    {
        [$r, $g, $b] = $this->hex($hex);

        return imagecolorallocate($im, $r, $g, $b);
    }

    /** Largura em pixéis que um texto ocupa, medida na própria fonte. */
    private function largura(string $font, int $size, string $texto): int
    {
        $box = imagettfbbox($size, 0, $font, $texto);

        return (int) abs($box[2] - $box[0]);
    }

    /** Altura em pixéis que um texto ocupa, medida na própria fonte. */
    private function altura(string $font, int $size, string $texto): int
    {
        $box = imagettfbbox($size, 0, $font, $texto);

        return (int) abs($box[7] - $box[1]);
    }

    /**
     * O GD não sabe desenhar rectângulos de cantos redondos: é um rectângulo por
     * cada eixo mais um círculo em cada canto.
     */
    private function rectanguloArredondado($im, int $x1, int $y1, int $x2, int $y2, int $raio, int $cor): void
    {
        $raio = (int) min($raio, ($x2 - $x1) / 2, ($y2 - $y1) / 2);

        if ($raio <= 0) {
            imagefilledrectangle($im, $x1, $y1, $x2, $y2, $cor);

            return;
        }

        imagefilledrectangle($im, $x1 + $raio, $y1, $x2 - $raio, $y2, $cor);
        imagefilledrectangle($im, $x1, $y1 + $raio, $x2, $y2 - $raio, $cor);

        $d = $raio * 2;
        imagefilledellipse($im, $x1 + $raio, $y1 + $raio, $d, $d, $cor);
        imagefilledellipse($im, $x2 - $raio, $y1 + $raio, $d, $d, $cor);
        imagefilledellipse($im, $x1 + $raio, $y2 - $raio, $d, $d, $cor);
        imagefilledellipse($im, $x2 - $raio, $y2 - $raio, $d, $d, $cor);
    }

    private function renderCard(
        string $title,
        string $badge,
        string $out,
        ?string $pais = null,
        ?string $local = null
    ): void {
        $w = self::WIDTH;
        $h = self::HEIGHT;
        $padX = self::PAD_X;
        $font = $this->font();
        $tema = CoverTheme::paraPais($pais);

        $im = imagecreatetruecolor($w, $h);
        imagesavealpha($im, true);

        // Fundo em gradiente vertical, do tom mais escuro do tema para o mais claro.
        [$r1, $g1, $b1] = $this->hex($tema->fundoTopo());
        [$r2, $g2, $b2] = $this->hex($tema->fundoBase());
        for ($y = 0; $y < $h; $y++) {
            $t = $y / max(1, $h - 1);
            $col = imagecolorallocate(
                $im,
                (int) round($r1 + ($r2 - $r1) * $t),
                (int) round($g1 + ($g2 - $g1) * $t),
                (int) round($b1 + ($b2 - $b1) * $t)
            );
            imageline($im, 0, $y, $w, $y, $col);
        }

        // Clarão no canto superior direito, na cor de destaque do tema.
        imagealphablending($im, true);
        [$br, $bg, $bb] = $tema->brilho();
        $glowR = 460;
        $cx = $w - 90;
        $cy = 60;
        $steps = 70;
        for ($i = 0; $i < $steps; $i++) {
            $rad = (int) round($glowR * (1 - $i / $steps));
            $glow = imagecolorallocatealpha($im, $br, $bg, $bb, $tema->brilhoForca());
            imagefilledellipse($im, $cx, $cy, $rad, $rad, $glow);
        }

        $accent = $this->cor($im, $tema->destaque());
        $white = $this->cor($im, $tema->titulo());
        $muted = $this->cor($im, $tema->suave());

        // Barra de destaque encostada à esquerda.
        imagefilledrectangle($im, 0, 0, 12, $h, $accent);

        $cabecalhoBase = $tema->temLetreiro()
            ? $this->desenharLetreiro($im, $tema, $font, $accent)
            : $this->desenharLogotipo($im, $font, $white);

        $host = parse_url((string) config('app.url'), PHP_URL_HOST);
        $dominio = ($host && $host !== 'localhost') ? $host : 'angolaemprego.com';

        imagettftext($im, 15, 0, $padX + 2, $cabecalhoBase + 30, $muted, $font, $dominio);

        // Título: quebrado em linhas e encolhido até caber em quatro.
        $titleSize = 54;
        $minSize = 28;
        $maxW = $w - 2 * $padX;
        $lines = $this->wrapText($title, $font, $titleSize, $maxW);
        while (count($lines) > 4 && $titleSize > $minSize) {
            $titleSize -= 3;
            $lines = $this->wrapText($title, $font, $titleSize, $maxW);
        }

        $lineH = (int) round($titleSize * 1.32);
        $blockH = count($lines) * $lineH;

        // A localização conta para o bloco que se centra, senão o título ficaria
        // a meio e a linha do local a sobrar por baixo.
        $localTexto = $tema->mostrarLocal() ? trim((string) $local) : '';
        $localSize = 30;
        $localAltura = $localTexto !== '' ? (int) round($localSize * 1.6) : 0;

        $regionTop = $cabecalhoBase + 30;
        $startY = (int) round($regionTop + (($h - $regionTop - $blockH - $localAltura) / 2));

        $y = $startY + $titleSize;
        foreach ($lines as $line) {
            imagettftext($im, $titleSize, 0, $padX, $y, $white, $font, $line);
            $y += $lineH;
        }

        if ($localTexto !== '') {
            $this->desenharLocal($im, $font, $localTexto, $padX, $y + $localSize - 6, $accent, $maxW, $localSize);
        }

        // Etiqueta pequena no canto inferior direito ("VAGA" / "ARTIGO"). Não se
        // desenha quando o cabeçalho já é um letreiro a dizer o mesmo em grande.
        if ($badge !== '' && !$tema->temLetreiro()) {
            $badgeText = mb_strtoupper($badge);
            $badgeSize = 20;
            $textW = $this->largura($font, $badgeSize, $badgeText);
            $bx2 = $w - $padX;
            $by2 = $h - 46;
            $padB = 12;
            imagefilledrectangle($im, $bx2 - $textW - 2 * $padB, $by2 - $badgeSize - $padB, $bx2, $by2 + $padB, $accent);
            imagettftext($im, $badgeSize, 0, $bx2 - $textW - $padB, $by2, $white, $font, $badgeText);
        }

        imagepng($im, $out);
        imagedestroy($im);
    }

    /**
     * Logótipo do site no cimo do cartão.
     *
     * @return int  a altura a que o cabeçalho acaba
     */
    private function desenharLogotipo($im, string $font, int $white): int
    {
        $padX = self::PAD_X;
        $topY = self::TOP_Y;
        $logoH = self::LOGO_HEIGHT;
        $logoPath = public_path('assets/img/logo-og.png');

        if (is_file($logoPath)) {
            $logo = @imagecreatefrompng($logoPath);

            if ($logo) {
                $logoW = (int) round($logoH * imagesx($logo) / imagesy($logo));
                imagealphablending($im, true);
                imagecopyresampled($im, $logo, $padX, $topY, 0, 0, $logoW, $logoH, imagesx($logo), imagesy($logo));
                imagedestroy($logo);

                return $topY + $logoH;
            }
        }

        // Sem o ficheiro do logótipo o cartão ficaria sem marca nenhuma; nesse
        // caso escreve-se o nome do site, que era o que se fazia antes.
        $marca = config('app.name', 'Angola Emprego');
        imagettftext($im, 30, 0, $padX, $topY + (int) round($logoH * 0.62), $white, $font, $marca);

        return $topY + $logoH;
    }

    /**
     * Letreiro grande a dizer de que país é a vaga, no lugar do logótipo.
     *
     * @return int  a altura a que o cabeçalho acaba
     */
    private function desenharLetreiro($im, CoverTheme $tema, string $font, int $fundo): int
    {
        $padX = self::PAD_X;
        $topY = self::TOP_Y;
        $texto = (string) $tema->letreiro();

        // Grande, mas nunca a passar da margem: em ecrãs de telemóvel o cartão
        // aparece reduzido e um letreiro cortado seria pior do que um mais pequeno.
        $tamanho = 46;
        $maxLargura = self::WIDTH - 2 * $padX;

        while ($tamanho > 26 && $this->largura($font, $tamanho, $texto) + 64 > $maxLargura) {
            $tamanho -= 2;
        }

        $largura = $this->largura($font, $tamanho, $texto);
        $altura = $this->altura($font, $tamanho, $texto);

        $padCaixa = 30;
        $padCima = 22;

        $x2 = $padX + $largura + 2 * $padCaixa;
        $y2 = $topY + $altura + 2 * $padCima;

        $this->rectanguloArredondado($im, $padX, $topY, $x2, $y2, 14, $fundo);

        imagettftext(
            $im,
            $tamanho,
            0,
            $padX + $padCaixa,
            $topY + $padCima + $altura,
            $this->cor($im, $tema->letreiroTexto()),
            $font,
            $texto
        );

        return $y2;
    }

    /**
     * Localização da vaga, por baixo do título, com um pino desenhado à frente.
     */
    private function desenharLocal($im, string $font, string $texto, int $x, int $baseline, int $cor, int $maxW, int $tamanho): void
    {
        // O pino é desenhado à mão porque a fonte do cartão não tem emojis: um
        // círculo com um bico por baixo, do tamanho da linha. O bico sai de
        // linhas horizontais cada vez mais curtas, e não de imagefilledpolygon,
        // cuja forma com o número de pontos está descontinuada desde o PHP 8.1.
        $raio = (int) round($tamanho * 0.28);
        $cx = $x + $raio;
        $cy = $baseline - (int) round($tamanho * 0.42);

        imagefilledellipse($im, $cx, $cy, $raio * 2, $raio * 2, $cor);

        $bico = $raio * 2;
        for ($i = 0; $i <= $bico; $i++) {
            $meia = (int) round($raio * (1 - $i / $bico));
            imageline($im, $cx - $meia, $cy + $i, $cx + $meia, $cy + $i, $cor);
        }

        $textoX = $cx + $raio + 14;
        $disponivel = $maxW - ($textoX - $x);

        // Uma morada comprida corta-se em vez de sair do cartão.
        while (mb_strlen($texto) > 4 && $this->largura($font, $tamanho, $texto) > $disponivel) {
            $texto = mb_substr($texto, 0, mb_strlen($texto) - 2) . '…';
        }

        imagettftext($im, $tamanho, 0, $textoX, $baseline, $cor, $font, $texto);
    }
}

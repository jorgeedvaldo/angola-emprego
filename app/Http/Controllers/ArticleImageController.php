<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;

/**
 * Generates a branded cover image for a post/job title entirely in pure PHP
 * via the GD extension — no system binaries, headless browsers, or external
 * services required. Mirrors the ToolPDF cover-image generator.
 */
class ArticleImageController extends Controller
{
    private const WIDTH = 1200;
    private const HEIGHT = 630;

    /**
     * Render a branded cover card for the given title and store it.
     *
     * @return string relative storage path (relative to storage/app/public)
     */
    public function generate(string $title, string $directory = 'images/posts', string $badge = ''): string
    {
        $dir = storage_path('app/public/' . $directory);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $relative = $directory . '/' . (string) Str::uuid() . '.png';
        $this->renderCard($title, $badge, storage_path('app/public/' . $relative));

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

    private function renderCard(string $title, string $badge, string $out): void
    {
        $w = self::WIDTH;
        $h = self::HEIGHT;
        $font = $this->font();

        $im = imagecreatetruecolor($w, $h);
        imagesavealpha($im, true);

        // Vertical brand gradient: dark navy → deep blue
        [$r1, $g1, $b1] = $this->hex('#0b1526');
        [$r2, $g2, $b2] = $this->hex('#10233d');
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

        // Soft accent-blue glow in the top-right corner
        imagealphablending($im, true);
        $glowR = 460;
        $cx = $w - 90;
        $cy = 60;
        $steps = 70;
        for ($i = 0; $i < $steps; $i++) {
            $rad = (int) round($glowR * (1 - $i / $steps));
            $glow = imagecolorallocatealpha($im, 16, 110, 234, 123);
            imagefilledellipse($im, $cx, $cy, $rad, $rad, $glow);
        }

        $accent = imagecolorallocate($im, 16, 110, 234);
        $white = imagecolorallocate($im, 255, 255, 255);
        $muted = imagecolorallocate($im, 170, 190, 220);

        // Left accent bar
        imagefilledrectangle($im, 0, 0, 12, $h, $accent);

        $padX = 70;
        $topY = 70;

        // Logo + brand wordmark
        $logoSize = 92;
        $logoPath = public_path('assets/img/logo.png');
        if (is_file($logoPath)) {
            $logo = @imagecreatefrompng($logoPath);
            if ($logo) {
                imagealphablending($im, true);
                imagecopyresampled($im, $logo, $padX, $topY, 0, 0, $logoSize, $logoSize, imagesx($logo), imagesy($logo));
                imagedestroy($logo);
            }
        }
        $brand = config('app.name', 'Angola Emprego');
        $host = parse_url((string) config('app.url'), PHP_URL_HOST);
        $subtitle = ($host && $host !== 'localhost') ? $host : 'Angola Emprego';

        imagettftext($im, 30, 0, $padX + $logoSize + 22, $topY + (int) round($logoSize * 0.42), $white, $font, $brand);
        imagettftext($im, 15, 0, $padX + $logoSize + 23, $topY + (int) round($logoSize * 0.78), $muted, $font, $subtitle);

        // Title — wrapped and auto-shrunk to fit at most 4 lines
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
        $regionTop = $topY + $logoSize;
        $startY = (int) round($regionTop + (($h - $regionTop - $blockH) / 2));
        $y = $startY + $titleSize;
        foreach ($lines as $line) {
            imagettftext($im, $titleSize, 0, $padX, $y, $white, $font, $line);
            $y += $lineH;
        }

        // Optional badge bottom-right (e.g. "VAGA" / "ARTIGO")
        if ($badge !== '') {
            $badgeText = mb_strtoupper($badge);
            $badgeSize = 20;
            $box = imagettfbbox($badgeSize, 0, $font, $badgeText);
            $textW = abs($box[2] - $box[0]);
            $bx2 = $w - $padX;
            $by2 = $h - 46;
            $padB = 12;
            imagefilledrectangle($im, $bx2 - $textW - 2 * $padB, $by2 - $badgeSize - $padB, $bx2, $by2 + $padB, $accent);
            imagettftext($im, $badgeSize, 0, $bx2 - $textW - $padB, $by2, $white, $font, $badgeText);
        }

        imagepng($im, $out);
        imagedestroy($im);
    }
}

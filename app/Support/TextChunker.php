<?php

namespace App\Support;

/**
 * Parte o texto de um CV em blocos comparáveis.
 *
 * Um CV inteiro transformado num único vector fica dominado pelo que há de comum
 * a todos os CVs — morada, formação, "trabalho em equipa" — e a parte que
 * interessa (dois anos a fazer processamento salarial) dilui-se no meio. Comparado
 * bloco a bloco, basta que um bloco responda ao requisito para isso contar, que é
 * exactamente como uma pessoa lê um currículo.
 */
class TextChunker
{
    /** Cada chamada extra ao serviço de análise custa tempo: convém um tecto. */
    public const MAX_CHUNKS = 10;

    private const TARGET_LENGTH = 600;
    private const MIN_LENGTH = 60;

    /**
     * @return array<int, string>
     */
    public static function chunk(string $text, int $maxChunks = self::MAX_CHUNKS): array
    {
        $text = trim(preg_replace('/[ \t]+/u', ' ', $text));

        if ($text === '') {
            return [];
        }

        $chunks = [];
        $current = '';

        // Agrupa parágrafos até perto do tamanho alvo: assim um bloco tende a
        // corresponder a uma secção do CV (uma experiência, uma formação) em vez de
        // a uma linha solta sem contexto.
        foreach (preg_split('/\R{2,}|\R/u', $text) as $paragraph) {
            $paragraph = trim($paragraph);

            if ($paragraph === '') {
                continue;
            }

            if ($current !== '' && mb_strlen($current . ' ' . $paragraph, 'UTF-8') > self::TARGET_LENGTH) {
                $chunks[] = $current;
                $current = $paragraph;

                continue;
            }

            $current = $current === '' ? $paragraph : $current . ' ' . $paragraph;
        }

        if ($current !== '') {
            $chunks[] = $current;
        }

        // Um CV sem quebras de linha aproveitáveis vem num bloco só gigante; nesse
        // caso corta-se pelo tamanho, que é melhor do que não cortar de todo.
        if (count($chunks) === 1 && mb_strlen($chunks[0], 'UTF-8') > self::TARGET_LENGTH * 1.5) {
            $chunks = self::splitByLength($chunks[0]);
        }

        $chunks = array_values(array_filter(
            $chunks,
            fn (string $chunk) => mb_strlen(trim($chunk), 'UTF-8') >= self::MIN_LENGTH
        ));

        if ($chunks === []) {
            return [mb_substr($text, 0, self::TARGET_LENGTH, 'UTF-8')];
        }

        return array_slice($chunks, 0, max(1, $maxChunks));
    }

    /**
     * @return array<int, string>
     */
    private static function splitByLength(string $text): array
    {
        $chunks = [];
        $length = mb_strlen($text, 'UTF-8');

        for ($offset = 0; $offset < $length; $offset += self::TARGET_LENGTH) {
            $chunks[] = mb_substr($text, $offset, self::TARGET_LENGTH, 'UTF-8');
        }

        return $chunks;
    }
}

<?php

namespace App\Support;

/**
 * Converte o HTML de um anúncio em texto que ainda tem a forma do original.
 *
 * As descrições das vagas são guardadas em HTML e não têm uma única quebra de
 * linha: os parágrafos e as listas são <p> e <li>. Um strip_tags() directo cola
 * tudo — "Recursos HumanosO Grupo Terra", "AngolaExperiência prática" — e apaga
 * a estrutura de que depende reconhecer onde começam os requisitos e onde acaba
 * cada um. Aqui as etiquetas de bloco viram quebras de linha antes de
 * desaparecerem, o que devolve ao texto a forma que o autor lhe deu.
 */
class PlainText
{
    /**
     * Etiquetas que, no ecrã, começam uma linha nova — e que por isso têm de
     * deixar uma quebra de linha atrás de si.
     */
    private const BLOCK_TAGS = 'p|div|li|ul|ol|h[1-6]|tr|table|section|article|blockquote|header|footer|dt|dd|pre';

    public static function fromHtml(?string $html): string
    {
        $text = (string) $html;

        $text = preg_replace('/<br\s*\/?>/i', "\n", $text);
        $text = preg_replace('#</(' . self::BLOCK_TAGS . ')\s*>#i', "\n", $text);
        $text = preg_replace('#<(' . self::BLOCK_TAGS . ')\b[^>]*>#i', "\n", $text);

        $text = html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8');

        // O espaço duro do &nbsp; não é apanhado por \s em todas as situações e
        // acabaria a colar-se às palavras.
        $text = str_replace("\u{00A0}", ' ', $text);

        return self::tidy($text);
    }

    /**
     * Uma linha por cada linha do original, sem espaços a mais e sem linhas
     * vazias — abrir e fechar de cada etiqueta deixaria uma quebra por cada, e
     * quem lê o texto a seguir trabalha linha a linha.
     */
    private static function tidy(string $text): string
    {
        $lines = [];

        foreach (preg_split('/\R/u', $text) as $line) {
            $line = trim(preg_replace('/[ \t]+/u', ' ', $line));

            if ($line !== '') {
                $lines[] = $line;
            }
        }

        return implode("\n", $lines);
    }
}

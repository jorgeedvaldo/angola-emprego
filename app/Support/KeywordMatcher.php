<?php

namespace App\Support;

/**
 * Complements VectorSimilarity::cosine() with an explicit term-matching signal.
 *
 * The multilingual embedding model has a "cross-lingual gap": it scores same-language
 * pairs higher than cross-language pairs of equivalent meaning, even when the
 * cross-language pair is the better match (e.g. a Portuguese job posting vs. an
 * English CV that literally contains the required certification). Matching the job's
 * own acronyms/technical terms/frequent words directly against the CV text is
 * language-agnostic for anything spelled the same way in both languages (acronyms,
 * proper nouns, standards), which covers most of the terms that actually matter for
 * technical/industrial roles.
 */
class KeywordMatcher
{
    private const STOPWORDS = [
        'a', 'o', 'as', 'os', 'de', 'da', 'do', 'das', 'dos', 'e', 'ou', 'em', 'no', 'na',
        'nos', 'nas', 'num', 'numa', 'para', 'por', 'com', 'sem', 'sob', 'sobre', 'entre',
        'ate', 'apos', 'ante', 'perante', 'um', 'uma', 'uns', 'umas', 'que', 'se', 'sua',
        'seu', 'suas', 'seus', 'ao', 'aos', 'a', 'as', 'e', 'sao', 'ser', 'estar', 'tem',
        'ter', 'como', 'mais', 'menos', 'muito', 'muita', 'muitos', 'muitas', 'pouco',
        'este', 'esta', 'estes', 'estas', 'esse', 'essa', 'esses', 'essas', 'isso', 'isto',
        'aquele', 'aquela', 'aqueles', 'aquelas', 'tambem', 'nao', 'sim', 'mas', 'porem',
        'quando', 'onde', 'qual', 'quais', 'cada', 'todo', 'toda', 'todos', 'todas',
        'outro', 'outra', 'outros', 'outras', 'pelo', 'pela', 'pelos', 'pelas', 'deste',
        'desta', 'destes', 'destas', 'nesse', 'nessa', 'nesses', 'nessas', 'neste', 'nesta',
        'nestes', 'nestas', 'seja', 'sejam', 'ser', 'ficar', 'pode', 'podem', 'podera',
        'poderao', 'deve', 'devem', 'devera', 'deverao', 'the', 'and', 'or', 'for', 'with',
        'without', 'from', 'this', 'that', 'these', 'those', 'will', 'shall', 'must',
        'have', 'has', 'are', 'is', 'be', 'been', 'being', 'anos', 'ano', 'vaga', 'empresa',
        'trabalho', 'candidato', 'candidatos', 'candidatura', 'candidaturas', 'funcao',
        'funcoes', 'area', 'sector', 'setor',
    ];

    /**
     * @return array<int, array{term: string, weight: int}>
     */
    public static function extractKeywords(string $text): array
    {
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8');

        $keywords = [];

        foreach (self::extractAcronyms($text) as $term) {
            $keywords[self::normalize($term)] = ['term' => $term, 'weight' => 2];
        }

        foreach (self::extractCapitalizedPhrases($text) as $term) {
            $key = self::normalize($term);
            if (!isset($keywords[$key])) {
                $keywords[$key] = ['term' => $term, 'weight' => 2];
            }
        }

        foreach (self::extractSignificantWords($text) as $term => $count) {
            $key = self::normalize($term);
            if (!isset($keywords[$key])) {
                $keywords[$key] = ['term' => $term, 'weight' => 1];
            }
        }

        return array_values($keywords);
    }

    /**
     * @param array<int, array{term: string, weight: int}> $keywords
     * @return array{score: float, matched: array<int, string>, total: int}|null
     */
    public static function score(array $keywords, ?string $cvText): ?array
    {
        if (empty($keywords) || !$cvText) {
            return null;
        }

        $normalizedCv = self::normalize($cvText);

        $totalWeight = 0;
        $matchedWeight = 0;
        $matched = [];

        foreach ($keywords as $keyword) {
            $totalWeight += $keyword['weight'];
            $needle = self::normalize($keyword['term']);

            if ($needle !== '' && str_contains($normalizedCv, $needle)) {
                $matchedWeight += $keyword['weight'];
                $matched[] = $keyword['term'];
            }
        }

        if ($totalWeight === 0) {
            return null;
        }

        return [
            'score' => $matchedWeight / $totalWeight,
            'matched' => $matched,
            'total' => count($keywords),
        ];
    }

    /**
     * Combines the semantic (embedding) score with the keyword score. Weighted evenly:
     * the semantic score captures overall topical similarity, the keyword score catches
     * literal requirement matches the embedding model under-weights across languages.
     */
    public static function blend(?float $semanticScore, ?float $keywordScore): ?float
    {
        if ($semanticScore === null && $keywordScore === null) {
            return null;
        }

        if ($semanticScore === null) {
            return $keywordScore;
        }

        if ($keywordScore === null) {
            return $semanticScore;
        }

        return (0.5 * $semanticScore) + (0.5 * $keywordScore);
    }

    /**
     * Fully uppercase tokens like IADC, BOP, HSE, NR-33, ISO9001 — the kind of
     * certification/standard names that matter most for technical requirements and
     * are usually spelled identically regardless of the surrounding language.
     *
     * @return array<int, string>
     */
    private static function extractAcronyms(string $text): array
    {
        preg_match_all('/\b[A-Z][A-Z0-9\-]{1,9}\b/u', $text, $matches);

        $acronyms = [];

        foreach (array_unique($matches[0]) as $match) {
            if (strlen(preg_replace('/[^A-Z]/', '', $match)) >= 2) {
                $acronyms[] = $match;
            }
        }

        return $acronyms;
    }

    /**
     * Multi-word Title Case sequences like "Well Sharp" or "Blowout Preventer" —
     * catches named certifications/equipment that aren't single acronyms.
     *
     * @return array<int, string>
     */
    private static function extractCapitalizedPhrases(string $text): array
    {
        preg_match_all('/\b(\p{Lu}\p{Ll}+(?:\s+\p{Lu}\p{Ll}+){1,3})\b/u', $text, $matches);

        return array_values(array_unique(array_map('trim', $matches[0])));
    }

    /**
     * Frequent, non-trivial words from the description — a language-agnostic proxy
     * for "what this posting keeps talking about" when no acronyms/phrases apply.
     *
     * @return array<string, int>
     */
    private static function extractSignificantWords(string $text): array
    {
        $normalized = mb_strtolower($text, 'UTF-8');

        preg_match_all('/\p{L}[\p{L}\p{N}]{3,}/u', $normalized, $matches);

        $counts = [];

        foreach ($matches[0] as $word) {
            if (in_array($word, self::STOPWORDS, true)) {
                continue;
            }

            $counts[$word] = ($counts[$word] ?? 0) + 1;
        }

        arsort($counts);

        return array_slice($counts, 0, 25, true);
    }

    private static function normalize(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');

        $text = strtr($text, [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n',
        ]);

        return trim(preg_replace('/\s+/', ' ', $text));
    }
}

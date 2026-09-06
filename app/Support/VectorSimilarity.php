<?php

namespace App\Support;

class VectorSimilarity
{
    public const DIMENSIONS = 384;

    // Must match MODEL_ID in public/assets/js/cv-analysis.js. Multilingual (covers
    // Portuguese) — all-MiniLM-L6-v2 is English-only and scores unrelated PT text
    // as artificially similar.
    public const MODEL_ID = 'Xenova/paraphrase-multilingual-MiniLM-L12-v2';

    public static function cosine(array $a, array $b): ?float
    {
        if (count($a) !== count($b) || $a === []) {
            return null;
        }

        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($a as $i => $value) {
            $dot += $value * $b[$i];
            $normA += $value * $value;
            $normB += $b[$i] * $b[$i];
        }

        if ($normA <= 0.0 || $normB <= 0.0) {
            return null;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }

    public static function isValidVector($vector): bool
    {
        return is_array($vector)
            && count($vector) === self::DIMENSIONS
            && array_reduce($vector, fn ($valid, $value) => $valid && is_numeric($value), true);
    }
}

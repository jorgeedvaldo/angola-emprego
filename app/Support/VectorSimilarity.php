<?php

namespace App\Support;

class VectorSimilarity
{
    public const DIMENSIONS = 768;

    // Must match MODEL_ID in analisecv-service's config.js — vectors from different
    // models aren't comparable. LaBSE (Language-Agnostic BERT Sentence Embeddings) is
    // trained specifically to place same-meaning sentences close together regardless
    // of language, which paraphrase-multilingual-MiniLM-L12-v2 (the previous model)
    // did not do well enough: it still scored same-language pairs higher than
    // cross-language pairs of equivalent meaning, distorting rankings whenever a job
    // posting (Portuguese) was compared against a CV written in English.
    public const MODEL_ID = 'Xenova/LaBSE';

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

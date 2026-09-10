<?php

namespace App\Support;

/**
 * Avalia um CV requisito a requisito, em vez de o comparar como um todo.
 *
 * Para cada requisito da vaga procura-se o bloco do CV que melhor lhe responde
 * (semelhança semântica) e verifica-se se os termos desse requisito aparecem
 * mesmo no texto (palavras-chave). As duas coisas combinadas dão, por requisito,
 * um veredicto que se pode mostrar ao recrutador — "Domínio avançado de Excel:
 * cumpre", "ERP Primavera: não encontrado" — e a média dá a compatibilidade
 * final. É isto que separa um candidato da área de um CV genérico bem escrito,
 * coisa que a comparação documento-a-documento não conseguia fazer.
 */
class RequirementMatcher
{
    public const MET = 'cumpre';
    public const PARTIAL = 'parcial';
    public const MISSING = 'ausente';

    /**
     * Limiares por calibrar com dados reais: são o ponto onde a semelhança deixa
     * de ser "assunto parecido" e passa a ser "isto responde ao requisito". Valem
     * para a escala do modelo em VectorSimilarity::MODEL_ID e devem ser revistos
     * se o modelo mudar.
     */
    private const MET_THRESHOLD = 0.60;
    private const PARTIAL_THRESHOLD = 0.45;

    /**
     * Peso de um requisito marcado como diferencial/desejável. Conta — quem o tem
     * fica à frente — mas não afunda quem não o tem, que é o que o anúncio quer
     * dizer quando escreve "Diferencial:".
     */
    private const OPTIONAL_WEIGHT = 0.35;

    /**
     * @param array<int, array{text: string, vector: array<int, float>}> $requirements
     * @param array<int, array<int, float>> $chunkVectors blocos do CV, já em vector
     * @return array{score: float|null, requirements: array<int, array{text: string, score: float|null, status: string, matched: array<int, string>}>}
     */
    public static function evaluate(array $requirements, array $chunkVectors, ?string $cvText): array
    {
        $evaluated = [];
        $weightedSum = 0.0;
        $totalWeight = 0.0;

        foreach ($requirements as $requirement) {
            $semantic = self::bestChunkSimilarity($requirement['vector'], $chunkVectors);

            $keywordResult = KeywordMatcher::score(
                KeywordMatcher::extractKeywordsFromRequirements($requirement['text']),
                $cvText
            );

            $score = KeywordMatcher::blend($semantic, $keywordResult['score'] ?? null);

            // Se o próprio anúncio diz que é um diferencial, é o anúncio que manda:
            // o peso vem do texto do requisito, não de nada que o browser envie.
            $optional = JobRequirements::isOptional($requirement['text']);
            $weight = $optional ? self::OPTIONAL_WEIGHT : 1.0;

            if ($score !== null) {
                $weightedSum += $score * $weight;
                $totalWeight += $weight;
            }

            $evaluated[] = [
                'text' => $requirement['text'],
                'score' => $score,
                'status' => self::status($score),
                'optional' => $optional,
                'matched' => array_slice($keywordResult['matched'] ?? [], 0, 6),
            ];
        }

        return [
            'score' => $totalWeight > 0.0 ? $weightedSum / $totalWeight : null,
            'requirements' => $evaluated,
        ];
    }

    /**
     * O melhor bloco ganha: um CV cumpre um requisito se houver ao menos um sítio
     * onde o responde, mesmo que o resto do currículo fale de outra coisa.
     *
     * @param array<int, float> $requirementVector
     * @param array<int, array<int, float>> $chunkVectors
     */
    private static function bestChunkSimilarity(array $requirementVector, array $chunkVectors): ?float
    {
        $best = null;

        foreach ($chunkVectors as $chunkVector) {
            $similarity = VectorSimilarity::cosine($requirementVector, $chunkVector);

            if ($similarity !== null && ($best === null || $similarity > $best)) {
                $best = $similarity;
            }
        }

        return $best;
    }

    private static function status(?float $score): string
    {
        if ($score === null || $score < self::PARTIAL_THRESHOLD) {
            return self::MISSING;
        }

        return $score >= self::MET_THRESHOLD ? self::MET : self::PARTIAL;
    }
}

<?php

namespace App\Support;

/**
 * Separa um anúncio de emprego nos requisitos individuais que ele pede.
 *
 * Comparar o anúncio inteiro com o CV inteiro mede pouco: a maior parte do texto
 * de uma vaga (quem é a empresa, o convite, para onde enviar o currículo) é igual
 * em qualquer anúncio, e a maior parte de um CV não responde a requisito nenhum.
 * Partindo a vaga em linhas — "Domínio avançado de Excel", "Conhecimento do ERP
 * Primavera" — passa a ser possível dizer o que o candidato cumpre e o que não
 * cumpre, em vez de uma percentagem única sem explicação.
 */
class JobRequirements
{
    /** Acima disto a lista deixa de caber no ecrã e o custo da análise dispara. */
    public const MAX_LINES = 10;

    /**
     * Serve apenas para rejeitar uma secção vazia. Pode ser tão baixo porque quem
     * a detecta já exige um título numa linha só sua — e uma vaga pode mesmo ter um
     * único requisito curto ("Domínio de Excel"), que não deve ser descartado.
     */
    private const MIN_SECTION_LENGTH = 10;

    private const MIN_LINE_LENGTH = 12;
    private const MAX_LINE_LENGTH = 240;

    /**
     * Marcadores de lista e numeração no início da linha, que não fazem parte do
     * requisito em si.
     */
    private const BULLETS = '/^[\s\x{2022}\x{25CF}\x{25AA}\x{00B7}\-\*\+\x{2013}\x{2014}o]+|^\s*\d+[\.\)]\s*/u';

    /**
     * @return array<int, string>
     */
    public static function lines(string $description): array
    {
        $text = PlainText::fromHtml($description);

        // Só há requisitos para listar se o anúncio tiver mesmo uma secção de
        // requisitos. Sem ela, partir o texto todo em linhas daria uma checklist
        // de frases soltas ("O Grupo Terra está a recrutar…") que não são
        // requisito nenhum — nesse caso é melhor não prometer o que não se sabe,
        // e quem chama volta à comparação do anúncio inteiro.
        $section = trim(KeywordMatcher::requirementsSection($text));

        if (mb_strlen($section, 'UTF-8') < self::MIN_SECTION_LENGTH) {
            return [];
        }

        $lines = [];

        foreach (preg_split('/\R+/u', $section) as $line) {
            $line = trim(preg_replace(self::BULLETS, '', $line));
            $line = trim(preg_replace('/\s+/u', ' ', $line));

            if (mb_strlen($line, 'UTF-8') < self::MIN_LINE_LENGTH) {
                continue;
            }

            // Um cabeçalho ("Requisitos", "Perfil pretendido") não é um requisito.
            if (KeywordMatcher::isRequirementHeading($line) || self::isAdministrative($line)) {
                continue;
            }

            $line = mb_substr($line, 0, self::MAX_LINE_LENGTH, 'UTF-8');

            if (!in_array($line, $lines, true)) {
                $lines[] = $line;
            }

            if (count($lines) >= self::MAX_LINES) {
                break;
            }
        }

        return $lines;
    }

    /**
     * Marcas de "isto é um plus, não uma exigência". Um anúncio que diz
     * "Diferencial: ERP Primavera" está a dizer que o candidato ideal sabe
     * Primavera, não que quem não sabe está fora — tratar as duas coisas por
     * igual afundava bons candidatos por causa de um extra.
     */
    private const OPTIONAL_MARKERS = [
        'diferencial', 'desejavel', 'preferencial', 'preferencialmente', 'valorizado',
        'valoriza-se', 'mais-valia', 'mais valia', 'constitui vantagem',
        'e uma vantagem', 'sera uma vantagem', 'como vantagem', 'nice to have',
        'a plus', 'nao obrigatorio', 'facultativo', 'opcional',
    ];

    /**
     * Começos de linha que anunciam informação administrativa da vaga, não algo
     * que se exija ao candidato: prazos, referências internas, tipo de contrato.
     * Aparecem no meio das listas de requisitos dos anúncios reais e não fazem
     * sentido numa checklist — ninguém "cumpre" um prazo de candidatura.
     */
    private const NON_REQUIREMENT_PREFIXES = [
        'prazo', 'periodo de candidatura', 'data limite', 'data de encerramento',
        'referencia da vaga', 'referencia:', 'ref.', 'validade', 'local de trabalho',
        'tipo de contrato', 'contrato a termo', 'regime', 'numero de vagas',
        'salario', 'remuneracao', 'horario de trabalho', 'inicio previsto',
    ];

    private static function isAdministrative(string $line): bool
    {
        $normalized = self::normalize($line);

        foreach (self::NON_REQUIREMENT_PREFIXES as $prefix) {
            if (str_starts_with($normalized, $prefix)) {
                return true;
            }
        }

        return false;
    }

    public static function isOptional(string $line): bool
    {
        $normalized = self::normalize($line);

        foreach (self::OPTIONAL_MARKERS as $marker) {
            if (str_contains($normalized, $marker)) {
                return true;
            }
        }

        return false;
    }

    private static function normalize(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');

        return strtr($text, [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a',
            'é' => 'e', 'ê' => 'e', 'í' => 'i', 'ó' => 'o', 'õ' => 'o', 'ô' => 'o',
            'ú' => 'u', 'ç' => 'c',
        ]);
    }

    /**
     * O texto que vale a pena transformar em vector: a secção de requisitos quando
     * existe, o anúncio todo quando não existe. É o que substitui o anúncio
     * completo no embedding da vaga.
     */
    public static function requirementsText(string $description, ?string $title = null): string
    {
        $text = PlainText::fromHtml($description);
        $section = trim(KeywordMatcher::requirementsSection($text));
        $relevant = mb_strlen($section, 'UTF-8') >= self::MIN_SECTION_LENGTH ? $section : $text;

        // O título entra no que vai para o vector pela mesma razão por que entra no
        // vocabulário: é a linha que diz qual é a profissão.
        $title = trim(PlainText::fromHtml((string) $title));

        return $title === '' ? $relevant : $title . "\n" . $relevant;
    }
}

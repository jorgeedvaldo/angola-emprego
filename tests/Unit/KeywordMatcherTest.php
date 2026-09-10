<?php

namespace Tests\Unit;

use App\Support\KeywordMatcher;
use PHPUnit\Framework\TestCase;

class KeywordMatcherTest extends TestCase
{
    private const VAGA = <<<TXT
    Vaga para Técnico(a) de Recursos Humanos
    O Grupo Terra está a recrutar um(a) Técnico(a) de Recursos Humanos para integrar a sua equipa.
    Se tem paixão pela área e procura um novo desafio profissional, esta vaga é para si.

    Requisitos
    Experiência mínima de 2 anos na função
    Sólidos conhecimentos da Lei Geral do Trabalho de Angola
    Experiência prática em processamento salarial e administração de pessoal
    Domínio avançado de Excel
    Diferencial: Conhecimento do ERP Primavera.

    CANDIDATURAS
    Envie o seu currículo atualizado para: rh@exemplo.co.ao
    TXT;

    public function test_vocabulary_comes_from_the_requirements_not_from_the_blurb()
    {
        $terms = array_column(KeywordMatcher::extractKeywords(self::VAGA), 'term');

        $this->assertContains('processamento salarial', $terms);
        $this->assertContains('primavera', $terms);

        // Vocabulário do convite e do rodapé: aparece em qualquer anúncio e não
        // distingue candidatos.
        $this->assertNotContains('novo desafio', $terms);
        $this->assertNotContains('Grupo Terra', $terms);
        $this->assertNotContains('procura um novo', $terms);
    }

    public function test_phrases_do_not_cross_line_breaks()
    {
        $terms = array_column(KeywordMatcher::extractKeywords(self::VAGA), 'term');

        foreach ($terms as $term) {
            $this->assertStringNotContainsString("\n", $term);
        }

        // "Excel" está no fim de uma linha e "Diferencial" no início da seguinte:
        // juntos nunca poderiam ser encontrados em CV nenhum.
        $this->assertNotContains('Excel Diferencial', $terms);
        $this->assertNotContains('Requisitos Experiência', $terms);
    }

    public function test_a_matching_cv_reaches_most_of_the_available_weight()
    {
        $keywords = KeywordMatcher::extractKeywords(self::VAGA);

        $cv = 'Técnico de Recursos Humanos com 4 anos de experiência. Sólidos conhecimentos '
            . 'da Lei Geral do Trabalho de Angola. Experiência prática em processamento salarial '
            . 'e administração de pessoal. Domínio avançado de Excel e do ERP Primavera.';

        $result = KeywordMatcher::score($keywords, $cv);

        $this->assertGreaterThan(0.8, $result['score']);
    }

    public function test_an_unrelated_cv_scores_near_zero()
    {
        $keywords = KeywordMatcher::extractKeywords(self::VAGA);

        $cv = 'Técnica de Análises Clínicas com formação de Ensino Médio e 3 anos de '
            . 'experiência na clínica. Atendimento ao público, receção de pacientes, '
            . 'colheita de amostras e noções de enfermagem e biossegurança.';

        $result = KeywordMatcher::score($keywords, $cv);

        $this->assertLessThan(0.1, $result['score']);
    }

    public function test_the_two_signals_weigh_the_same()
    {
        $this->assertSame(0.5, KeywordMatcher::blend(1.0, 0.0));
        $this->assertSame(0.5, KeywordMatcher::blend(0.0, 1.0));
        $this->assertSame(0.8, KeywordMatcher::blend(0.6, 1.0));
    }

    public function test_a_single_signal_is_used_alone_when_the_other_is_missing()
    {
        $this->assertSame(0.7, KeywordMatcher::blend(0.7, null));
        $this->assertSame(0.4, KeywordMatcher::blend(null, 0.4));
        $this->assertNull(KeywordMatcher::blend(null, null));
    }
}

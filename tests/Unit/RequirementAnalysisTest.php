<?php

namespace Tests\Unit;

use App\Support\JobRequirements;
use App\Support\RequirementMatcher;
use App\Support\TextChunker;
use App\Support\VectorSimilarity;
use PHPUnit\Framework\TestCase;

class RequirementAnalysisTest extends TestCase
{
    private const VAGA = <<<TXT
    Vaga para Técnico(a) de Recursos Humanos
    O Grupo Terra está a recrutar um(a) Técnico(a) de Recursos Humanos para integrar a sua equipa.

    Requisitos
    Experiência mínima de 2 anos na função
    Sólidos conhecimentos da Lei Geral do Trabalho de Angola
    Domínio avançado de Excel
    Diferencial: Conhecimento do ERP Primavera.

    CANDIDATURAS
    Envie o seu currículo para: rh@exemplo.co.ao
    TXT;

    public function test_the_advert_is_split_into_the_requirements_it_asks_for()
    {
        $lines = JobRequirements::lines(self::VAGA);

        $this->assertSame([
            'Experiência mínima de 2 anos na função',
            'Sólidos conhecimentos da Lei Geral do Trabalho de Angola',
            'Domínio avançado de Excel',
            'Diferencial: Conhecimento do ERP Primavera.',
        ], $lines);
    }

    public function test_headings_and_boilerplate_are_left_out()
    {
        $lines = JobRequirements::lines(self::VAGA);

        foreach ($lines as $line) {
            $this->assertStringNotContainsString('@exemplo.co.ao', $line);
            $this->assertNotSame('Requisitos', $line);
        }
    }

    public function test_bullets_and_numbering_are_stripped()
    {
        $lines = JobRequirements::lines("Requisitos\n• Domínio avançado de Excel\n2) Carta de condução válida");

        $this->assertSame(['Domínio avançado de Excel', 'Carta de condução válida'], $lines);
    }

    public function test_an_advert_without_a_requirements_section_yields_no_lines_but_keeps_the_text()
    {
        $advert = 'Precisamos de alguém dinâmico para a nossa equipa comercial em Luanda.';

        $this->assertSame([], JobRequirements::lines($advert));
        $this->assertSame($advert, JobRequirements::requirementsText($advert));
    }

    public function test_only_the_requirements_go_into_the_job_vector()
    {
        $text = JobRequirements::requirementsText(self::VAGA);

        $this->assertStringContainsString('Domínio avançado de Excel', $text);
        $this->assertStringNotContainsString('Grupo Terra', $text);
        $this->assertStringNotContainsString('rh@exemplo.co.ao', $text);
    }

    public function test_a_cv_is_split_into_comparable_blocks()
    {
        $cv = "EXPERIÊNCIA\n" . str_repeat('Processamento salarial e administração de pessoal. ', 20)
            . "\n\nFORMAÇÃO\n" . str_repeat('Licenciatura em Gestão de Recursos Humanos. ', 20);

        $chunks = TextChunker::chunk($cv);

        $this->assertGreaterThan(1, count($chunks));
        $this->assertLessThanOrEqual(TextChunker::MAX_CHUNKS, count($chunks));
    }

    public function test_a_short_cv_stays_in_one_block()
    {
        $chunks = TextChunker::chunk('Técnico de Recursos Humanos com quatro anos de experiência em Luanda.');

        $this->assertCount(1, $chunks);
    }

    public function test_an_empty_cv_yields_no_blocks()
    {
        $this->assertSame([], TextChunker::chunk('   '));
    }

    public function test_each_requirement_gets_its_own_verdict()
    {
        $excel = $this->vector('excel');
        $primavera = $this->vector('primavera');

        $requirements = [
            ['text' => 'Domínio avançado de Excel', 'vector' => $excel],
            ['text' => 'Conhecimento do ERP Primavera', 'vector' => $primavera],
        ];

        // O CV tem um bloco sobre Excel e nada sobre Primavera.
        $chunkVectors = [$excel, $this->vector('atendimento')];
        $cvText = 'Domínio avançado de Excel, tabelas dinâmicas e macros.';

        $evaluation = RequirementMatcher::evaluate($requirements, $chunkVectors, $cvText);

        $this->assertSame(RequirementMatcher::MET, $evaluation['requirements'][0]['status']);
        $this->assertSame(RequirementMatcher::MISSING, $evaluation['requirements'][1]['status']);
        $this->assertGreaterThan(
            $evaluation['requirements'][1]['score'],
            $evaluation['requirements'][0]['score']
        );
    }

    public function test_the_best_block_decides_the_requirement()
    {
        $excel = $this->vector('excel');

        // O bloco certo está no meio de outros que não têm nada a ver: mesmo assim
        // o requisito conta como cumprido, tal como quando uma pessoa lê o CV.
        $evaluation = RequirementMatcher::evaluate(
            [['text' => 'Domínio avançado de Excel', 'vector' => $excel]],
            [$this->vector('cozinha'), $this->vector('futebol'), $excel],
            'Domínio avançado de Excel.'
        );

        $this->assertSame(RequirementMatcher::MET, $evaluation['requirements'][0]['status']);
    }

    public function test_the_overall_score_is_the_average_of_the_requirements()
    {
        $excel = $this->vector('excel');

        $evaluation = RequirementMatcher::evaluate(
            [
                ['text' => 'Domínio avançado de Excel', 'vector' => $excel],
                ['text' => 'Conhecimento do ERP Primavera', 'vector' => $this->vector('primavera')],
            ],
            [$excel],
            'Domínio avançado de Excel.'
        );

        $expected = ($evaluation['requirements'][0]['score'] + $evaluation['requirements'][1]['score']) / 2;

        $this->assertEqualsWithDelta($expected, $evaluation['score'], 0.0001);
    }

    public function test_a_differential_requirement_weighs_less_than_a_mandatory_one()
    {
        $this->assertTrue(JobRequirements::isOptional('Diferencial: Conhecimento do ERP Primavera'));
        $this->assertTrue(JobRequirements::isOptional('Carta de condução (preferencial)'));
        $this->assertFalse(JobRequirements::isOptional('Domínio avançado de Excel'));

        $excel = $this->vector('excel');
        $cumprido = ['text' => 'Domínio avançado de Excel', 'vector' => $excel];

        $comDiferencial = RequirementMatcher::evaluate(
            [$cumprido, ['text' => 'Diferencial: ERP Primavera', 'vector' => $this->vector('primavera')]],
            [$excel],
            'Domínio avançado de Excel.'
        );

        $comObrigatorio = RequirementMatcher::evaluate(
            [$cumprido, ['text' => 'Conhecimento do ERP Primavera', 'vector' => $this->vector('primavera')]],
            [$excel],
            'Domínio avançado de Excel.'
        );

        // Falhar um diferencial custa menos do que falhar uma exigência.
        $this->assertGreaterThan($comObrigatorio['score'], $comDiferencial['score']);
        $this->assertTrue($comDiferencial['requirements'][1]['optional']);
        $this->assertFalse($comObrigatorio['requirements'][1]['optional']);
    }

    /**
     * Vector determinístico por palavra: iguais têm coseno 1, diferentes 0.
     *
     * @return array<int, float>
     */
    private function vector(string $word): array
    {
        $vector = array_fill(0, VectorSimilarity::DIMENSIONS, 0.0);
        $vector[abs(crc32($word)) % VectorSimilarity::DIMENSIONS] = 1.0;

        return $vector;
    }
}

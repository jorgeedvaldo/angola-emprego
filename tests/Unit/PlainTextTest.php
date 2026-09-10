<?php

namespace Tests\Unit;

use App\Support\JobRequirements;
use App\Support\KeywordMatcher;
use App\Support\PlainText;
use PHPUnit\Framework\TestCase;

/**
 * As descrições das vagas são guardadas em HTML e não têm uma única quebra de
 * linha — o que só se descobre olhando para os dados reais. Um strip_tags()
 * directo colava tudo numa linha e nenhum requisito era lido; estes testes
 * seguram essa correcção.
 */
class PlainTextTest extends TestCase
{
    /** Uma vaga real, tal como está guardada na base de dados. */
    private const ADVERT_HTML = '<h2>Vaga para Técnico(a) de Recursos Humanos</h2>'
        . '<p>O <strong>Grupo Terra</strong> está a recrutar um(a) <strong>Técnico(a) de Recursos '
        . 'Humanos</strong> para integrar a sua equipa.</p>'
        . '<h3>Requisitos</h3><ul>'
        . '<li>Experiência mínima de 2 anos na função</li>'
        . '<li>Sólidos conhecimentos da Lei Geral do Trabalho de Angola</li>'
        . '<li>Experiência prática em processamento salarial e administração de pessoal</li>'
        . '<li>Domínio avançado de Excel</li></ul>'
        . '<p><strong>Diferencial:</strong> Conhecimento do ERP Primavera.</p>'
        . '<h3>CANDIDATURAS</h3><p>Envie o currículo para: rh@grupoterra.co.ao</p>'
        . '<p><em>Encontre aqui as melhores vagas de emprego para 2026.</em></p>'
        . '<p>Tags: emprego em Angola, Recrutamento 2026</p>';

    public function test_block_tags_become_line_breaks()
    {
        $text = PlainText::fromHtml('<h3>Requisitos</h3><ul><li>Excel</li><li>Primavera</li></ul>');

        $this->assertSame("Requisitos\nExcel\nPrimavera", $text);
    }

    public function test_words_are_not_glued_together_at_tag_boundaries()
    {
        $text = PlainText::fromHtml(self::ADVERT_HTML);

        // Sem esta conversão saía "Recursos HumanosO Grupo Terra" e
        // "AngolaExperiência prática" — palavras inventadas que nenhum CV contém.
        $this->assertStringNotContainsString('HumanosO', $text);
        $this->assertStringNotContainsString('AngolaExperiência', $text);
        $this->assertStringNotContainsString('RequisitosExperiência', $text);
    }

    public function test_line_breaks_and_entities_are_handled()
    {
        $this->assertSame("Primeira\nSegunda", PlainText::fromHtml('Primeira<br>Segunda'));
        $this->assertSame('Direcção & Gestão', PlainText::fromHtml('<p>Direc&ccedil;&atilde;o &amp; Gest&atilde;o</p>'));
        $this->assertSame('a b', PlainText::fromHtml("<p>a&nbsp;b</p>"));
    }

    public function test_requirements_are_read_from_a_real_html_advert()
    {
        $this->assertSame(
            [
                'Experiência mínima de 2 anos na função',
                'Sólidos conhecimentos da Lei Geral do Trabalho de Angola',
                'Experiência prática em processamento salarial e administração de pessoal',
                'Domínio avançado de Excel',
                'Diferencial: Conhecimento do ERP Primavera.',
            ],
            JobRequirements::lines(self::ADVERT_HTML)
        );
    }

    public function test_the_portal_footer_never_reaches_the_vocabulary()
    {
        $terms = array_column(KeywordMatcher::extractKeywords(self::ADVERT_HTML), 'term');

        // Este rodapé é acrescentado pelo portal a todos os anúncios.
        foreach (['Encontre', 'Tags', 'emprego em Angola', 'Grupo Terra'] as $noise) {
            $this->assertNotContains($noise, $terms);
        }

        $this->assertContains('processamento salarial', $terms);
    }

    public function test_the_job_title_separates_roles_with_identical_requirements()
    {
        // Caso encontrado nos anúncios reais: o mesmo empregador publicou vários
        // cargos com exactamente o mesmo bloco de requisitos. Sem o título, são
        // indistinguíveis.
        $shared = '<h3>Requisitos</h3><ul><li>Experiência comprovada na função a que se candidatam</li>'
            . '<li>Disponibilidade para trabalhar em regime Offshore ou Onshore</li></ul>';

        $nutricionista = KeywordMatcher::extractKeywords($shared, 'Vaga para Nutricionista');
        $curriculo = 'Nutricionista com experiência comprovada na função e disponibilidade para regime offshore.';

        $comTitulo = KeywordMatcher::score($nutricionista, $curriculo)['score'];
        $semTitulo = KeywordMatcher::score(KeywordMatcher::extractKeywords($shared), $curriculo)['score'];

        $this->assertContains('nutricionista', array_column($nutricionista, 'term'));
        $this->assertGreaterThan($semTitulo, $comTitulo);

        // E o CV de outra profissão, com os mesmos requisitos genéricos cumpridos,
        // fica abaixo de quem é mesmo da área.
        $outro = 'Técnico de manutenção com experiência comprovada na função e disponibilidade offshore.';
        $this->assertGreaterThan(
            KeywordMatcher::score($nutricionista, $outro)['score'],
            $comTitulo
        );
    }

    public function test_short_acronyms_from_the_title_are_kept()
    {
        $keywords = KeywordMatcher::extractKeywords(
            '<h3>Requisitos</h3><ul><li>Experiência comprovada na função</li></ul>',
            'Vaga para Técnico de IT'
        );

        $this->assertContains('IT', array_column($keywords, 'term'));

        // No corpo do anúncio as siglas de duas letras continuam a ser ruído.
        $noBody = KeywordMatcher::extractKeywords('<h3>Requisitos</h3><ul><li>Conhecimentos de IT e RH</li></ul>');
        $this->assertNotContains('IT', array_column($noBody, 'term'));
    }

    public function test_administrative_lines_are_not_requirements()
    {
        $lines = JobRequirements::lines(
            '<h3>Requisitos</h3><ul><li>Mínimo de 2 anos de experiência</li>'
            . '<li>Prazo de candidaturas: até 13 de Setembro de 2026</li>'
            . '<li>Referência da vaga: IT_PROG_0926</li>'
            . '<li>Regime presencial</li></ul>'
        );

        $this->assertSame(['Mínimo de 2 anos de experiência'], $lines);
    }
}

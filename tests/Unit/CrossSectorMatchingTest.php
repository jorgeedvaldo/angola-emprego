<?php

namespace Tests\Unit;

use App\Support\JobRequirements;
use App\Support\KeywordMatcher;
use PHPUnit\Framework\TestCase;

/**
 * A ferramenta tem de servir vagas, CVs e anúncios que nada têm a ver uns com os
 * outros — não apenas o caso que motivou os ajustes ao algoritmo.
 *
 * Este corpus varia de propósito o sector (recursos humanos, transporte, saúde,
 * informática, contabilidade, segurança offshore, panificação, armazém), o
 * formato do anúncio (títulos com e sem acentos, bullets, numeração, parágrafo
 * corrido sem títulos) e a língua. Cada anúncio tem de escolher o CV certo de
 * entre todos, e o "CV genérico" — bem escrito, cheio de vocabulário de
 * currículo e sem relação com vaga nenhuma — não pode pontuar em lado nenhum.
 */
class CrossSectorMatchingTest extends TestCase
{
    /**
     * @return array<string, string>
     */
    private function adverts(): array
    {
        return [
            'rh' => "Técnico(a) de Recursos Humanos\nO Grupo Terra está a recrutar para a sua equipa.\n\n"
                . "Requisitos\nExperiência mínima de 2 anos na função\n"
                . "Sólidos conhecimentos da Lei Geral do Trabalho de Angola\n"
                . "Experiência prática em processamento salarial e administração de pessoal\n"
                . "Domínio avançado de Excel\nDiferencial: Conhecimento do ERP Primavera.\n\n"
                . "CANDIDATURAS\nEnvie o currículo para: rh@exemplo.co.ao",

            'motorista' => "MOTORISTA DE PESADOS — LUANDA\n\nPerfil do candidato:\n"
                . "• Carta de condução categoria C e E válida\n"
                . "• Carta de qualificação de motorista (CAM) actualizada\n"
                . "• Experiência comprovada em condução de camiões de longo curso\n"
                . "• Conhecimento de tacógrafo digital e tempos de condução\n\n"
                . "Oferecemos: contrato de trabalho e subsídio de alimentação.",

            'enfermagem' => "Clínica admite Enfermeiro(a) para o serviço de urgência.\n\nRequisitos:\n"
                . "1) Licenciatura em Enfermagem\n"
                . "2) Inscrição válida na Ordem dos Enfermeiros de Angola\n"
                . "3) Experiência em triagem e administração de terapêutica endovenosa\n"
                . "4) Suporte Básico de Vida (SBV) actualizado\n\n"
                . "Como se candidatar: entregar CV na recepção.",

            'programador' => "Programador PHP / Laravel\n\nO que procuramos\n"
                . "- Experiência sólida em PHP 8 e no framework Laravel\n"
                . "- Domínio de MySQL, incluindo optimização de queries e índices\n"
                . "- Trabalho com APIs REST e integração de serviços externos\n"
                . "- Desejável: Vue.js e experiência com Docker\n\n"
                . "Benefícios: seguro de saúde e formação paga.",

            'hse' => "TÉCNICO DE SEGURANÇA (HSE) — OFFSHORE\n\nExige-se:\n"
                . "Curso técnico de Segurança e Higiene no Trabalho\n"
                . "Certificação BOSIET válida\nFormação NR-33 para espaços confinados\n"
                . "Experiência em plataformas offshore e elaboração de JSA\n\n"
                . "Contactos: recrutamento@exemplo.co.ao",

            'padeiro' => "Padaria admite Padeiro(a)\n\nQualificações:\n"
                . "Experiência comprovada em panificação e pastelaria\n"
                . "Conhecimento de fermentação natural e massas folhadas\n"
                . "Disponibilidade para trabalho nocturno\nCarteira de saúde actualizada\n\n"
                . "Benefícios:\nRefeição no local e transporte assegurado",

            'armazem' => "Warehouse Supervisor — Lobito\n\nRequirements:\n"
                . "Proven experience managing warehouse operations and stock control\n"
                . "Working knowledge of inventory management systems (WMS)\n"
                . "Forklift operating licence\n"
                . "Ability to lead a team of ten or more operators\n\n"
                . "Benefits: health insurance and annual bonus.",

            // Sem títulos nem lista: um parágrafo corrido, como muitos anúncios reais.
            'contabilista' => 'Precisamos de um contabilista sénior para o escritório em Benguela, '
                . 'responsável pelo fecho de contas mensal, pela declaração de IVA e do Imposto '
                . 'Industrial, pela reconciliação bancária e pelo contacto com a AGT. É indispensável '
                . 'ter cédula profissional da Ordem dos Contabilistas e experiência com o software '
                . 'Primavera.',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function cvs(): array
    {
        return [
            'rh' => 'Técnica de Recursos Humanos com 4 anos de experiência na função. Responsável pelo '
                . 'processamento salarial mensal e pela administração de pessoal: contratos, faltas e '
                . 'férias. Sólidos conhecimentos da Lei Geral do Trabalho de Angola. Domínio avançado '
                . 'de Excel com tabelas dinâmicas.',

            'motorista' => 'Motorista profissional com carta de condução categoria C e E e CAM '
                . 'actualizada. Oito anos de condução de camiões de longo curso entre Luanda e o '
                . 'Lubango. Habituado a tacógrafo digital e ao cumprimento dos tempos de condução.',

            'enfermagem' => 'Enfermeira com Licenciatura em Enfermagem e inscrição válida na Ordem dos '
                . 'Enfermeiros de Angola. Três anos no serviço de urgência, com triagem de doentes e '
                . 'administração de terapêutica endovenosa. Suporte Básico de Vida (SBV) renovado.',

            'programador' => 'Programador com experiência sólida em PHP 8 e Laravel. Domínio de MySQL, '
                . 'incluindo optimização de queries e criação de índices. Construção e consumo de APIs '
                . 'REST e integração de serviços externos de pagamento. Também Vue.js e Docker.',

            'hse' => 'Técnico de Segurança com curso técnico de Segurança e Higiene no Trabalho. '
                . 'Certificação BOSIET válida e formação NR-33 para espaços confinados. Seis anos em '
                . 'plataformas offshore, com elaboração de JSA e permissões de trabalho.',

            'padeiro' => 'Padeiro com experiência comprovada em panificação e pastelaria. Trabalho com '
                . 'fermentação natural e produção de massas folhadas. Disponibilidade para trabalho '
                . 'nocturno. Carteira de saúde actualizada.',

            'armazem' => 'Warehouse Supervisor with proven experience managing warehouse operations and '
                . 'stock control for a distribution centre. Working knowledge of inventory management '
                . 'systems (WMS). Valid forklift operating licence. Led a team of twelve operators.',

            'contabilista' => 'Contabilista com cédula profissional da Ordem dos Contabilistas. '
                . 'Responsável pelo fecho de contas mensal e pela reconciliação bancária. Preparação da '
                . 'declaração de IVA e do Imposto Industrial junto da AGT. Trabalho diário com o '
                . 'software Primavera.',

            // Bem escrito, cheio de vocabulário de currículo, sem relação com vaga nenhuma.
            // Era este perfil que aparecia lado a lado com candidatos da área.
            'generico' => 'Técnica de Análises Clínicas com Ensino Médio e 3 anos de experiência na '
                . 'clínica. Atendimento ao público, receção de pacientes e registo de dados, sempre com '
                . 'empatia, organização e profissionalismo. Destaco-me pela pontualidade, '
                . 'responsabilidade, boa apresentação e facilidade de adaptação a diferentes ambientes '
                . 'de trabalho. Trabalho em equipa e resolução de problemas.',
        ];
    }

    public function test_each_advert_picks_its_own_candidate()
    {
        foreach ($this->adverts() as $sector => $advert) {
            $keywords = KeywordMatcher::extractKeywords($advert);
            $scores = [];

            foreach ($this->cvs() as $candidate => $cv) {
                $scores[$candidate] = KeywordMatcher::score($keywords, $cv)['score'] ?? 0.0;
            }

            arsort($scores);

            $this->assertSame(
                $sector,
                array_key_first($scores),
                sprintf('A vaga de %s escolheu o CV de %s.', $sector, array_key_first($scores))
            );
        }
    }

    public function test_a_generic_cv_matches_no_advert()
    {
        $generic = $this->cvs()['generico'];

        foreach ($this->adverts() as $sector => $advert) {
            $score = KeywordMatcher::score(KeywordMatcher::extractKeywords($advert), $generic)['score'] ?? 0.0;

            $this->assertLessThan(
                0.05,
                $score,
                sprintf('O CV genérico pontuou %.3f na vaga de %s.', $score, $sector)
            );
        }
    }

    public function test_the_right_candidate_stands_clearly_apart_from_the_rest()
    {
        foreach ($this->adverts() as $sector => $advert) {
            $keywords = KeywordMatcher::extractKeywords($advert);
            $correct = KeywordMatcher::score($keywords, $this->cvs()[$sector])['score'] ?? 0.0;

            foreach ($this->cvs() as $candidate => $cv) {
                if ($candidate === $sector) {
                    continue;
                }

                $other = KeywordMatcher::score($keywords, $cv)['score'] ?? 0.0;

                $this->assertGreaterThan(
                    $other + 0.3,
                    $correct,
                    sprintf('Na vaga de %s, %s ficou demasiado perto do candidato certo.', $sector, $candidate)
                );
            }
        }
    }

    public function test_requirements_are_read_from_every_advert_format()
    {
        // Bullets, numeração, títulos com e sem acentos, e inglês.
        $expected = [
            'rh' => 5,
            'motorista' => 4,
            'enfermagem' => 4,
            'programador' => 4,
            'hse' => 4,
            'padeiro' => 4,
            'armazem' => 4,
        ];

        foreach ($expected as $sector => $count) {
            $this->assertCount(
                $count,
                JobRequirements::lines($this->adverts()[$sector]),
                sprintf('Requisitos lidos da vaga de %s.', $sector)
            );
        }
    }

    public function test_what_the_company_offers_never_becomes_a_requirement()
    {
        foreach ($this->adverts() as $sector => $advert) {
            foreach (JobRequirements::lines($advert) as $line) {
                foreach (['seguro de saúde', 'subsídio de alimentação', 'health insurance',
                          'Refeição no local', 'formação paga', 'annual bonus'] as $perk) {
                    $this->assertStringNotContainsStringIgnoringCase(
                        $perk,
                        $line,
                        sprintf('A vaga de %s meteu um benefício na lista de requisitos.', $sector)
                    );
                }
            }
        }
    }

    public function test_a_paragraph_advert_has_no_checklist_but_still_matches()
    {
        $advert = $this->adverts()['contabilista'];

        // Sem secção de requisitos não há checklist honesta a mostrar…
        $this->assertSame([], JobRequirements::lines($advert));

        // …mas a comparação continua a escolher o candidato certo.
        $keywords = KeywordMatcher::extractKeywords($advert);
        $correct = KeywordMatcher::score($keywords, $this->cvs()['contabilista'])['score'] ?? 0.0;
        $generic = KeywordMatcher::score($keywords, $this->cvs()['generico'])['score'] ?? 0.0;

        $this->assertGreaterThan(0.4, $correct);
        $this->assertLessThan(0.05, $generic);
    }

    /**
     * Formatos de anúncio que partiam o leitor de requisitos. Cada um destes veio
     * de uma falha real encontrada a passar o corpus por várias formatações.
     */
    public function test_awkward_advert_formats_are_read_correctly()
    {
        // Um título de secção só conta quando está numa linha só sua: a palavra
        // "perfil" a meio de uma frase de marketing não abre a lista de requisitos.
        $this->assertSame(
            ['Experiência em atendimento ao público', 'Domínio de Word e Excel', 'Inglês falado'],
            JobRequirements::lines(
                "Vaga de Recepcionista
Procuramos alguém com o perfil certo para receber os nossos "
                . "clientes na sede.

Requisitos
Experiência em atendimento ao público
"
                . "Domínio de Word e Excel
Inglês falado"
            )
        );

        // Título e primeiro requisito na mesma linha.
        $this->assertSame(
            ['frequência universitária em Economia', 'Disponibilidade imediata'],
            JobRequirements::lines(
                "Vaga de Estagiário

Requisitos: frequência universitária em Economia
"
                . 'Disponibilidade imediata'
            )
        );

        // Lista curta, mas verdadeira.
        $this->assertSame(
            ['Cinco anos de obra', 'Leitura de plantas simples'],
            JobRequirements::lines(
                "Empresa admite Pedreiro.

Requisitos:
Cinco anos de obra
Leitura de plantas simples"
            )
        );

        // Travessões longos como marcador de lista.
        $this->assertSame(
            ['Curso técnico de electricidade', 'Leitura de esquemas eléctricos'],
            JobRequirements::lines(
                "Electricista

Exigências
— Curso técnico de electricidade
— Leitura de esquemas eléctricos"
            )
        );
    }

    public function test_accented_section_headings_are_recognised()
    {
        // As listas de títulos estão escritas sem acentos; se a procura não os
        // ignorar também, "Qualificações", "Competências", "Exigências" e
        // "Benefícios" passam todos ao lado.
        foreach (['Qualificações', 'Competências', 'Exigências'] as $heading) {
            $this->assertSame(
                ['Domínio de Excel e Word', 'Redacção de ofícios'],
                JobRequirements::lines("Assistente

$heading:
Domínio de Excel e Word
Redacção de ofícios"),
                sprintf('Título "%s" não foi reconhecido.', $heading)
            );
        }

        $this->assertSame(
            ['Domínio de Excel'],
            JobRequirements::lines("Assistente

Requisitos:
Domínio de Excel

Benefícios:
Seguro de saúde")
        );
    }

    public function test_optional_markers_do_not_fire_on_ordinary_words()
    {
        $this->assertTrue(JobRequirements::isOptional('Diferencial: Conhecimento do ERP Primavera'));
        $this->assertTrue(JobRequirements::isOptional('Desejável: Vue.js e experiência com Docker'));
        $this->assertTrue(JobRequirements::isOptional('Carta de condução (preferencial)'));

        // "bónus" e "vantagem" soltos aparecem no que a empresa oferece e em
        // requisitos a sério — não bastam para marcar a linha como dispensável.
        $this->assertFalse(JobRequirements::isOptional('Benefits: health insurance and annual bonus'));
        $this->assertFalse(JobRequirements::isOptional('Capacidade de tirar vantagem competitiva do mercado'));
        $this->assertFalse(JobRequirements::isOptional('Domínio avançado de Excel'));
    }
}

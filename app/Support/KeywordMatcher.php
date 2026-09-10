<?php

namespace App\Support;

/**
 * Complements VectorSimilarity::cosine() with an explicit term-matching signal.
 *
 * Even a cross-lingual-aware embedding model (LaBSE) can under-weight a literal
 * requirement match (an exact certification code, standard, or technical term) that
 * appears identically in the job description and the CV. This class extracts a wide,
 * weighted vocabulary from the job description — acronyms, technical phrases,
 * multi-word bigrams, and significant single words, boosted when they fall inside a
 * detected "Requisitos"-style section — and checks it against the CV text.
 *
 * The wider vocabulary (see MAX_WORDS) is deliberately balanced by three precision
 * mechanisms so a bigger word pool doesn't dilute the signal the way a flat/unweighted
 * pool would: (1) length-tiered weights, so longer/rarer words count for more than
 * short filler; (2) a large, curated stopword list of generic recruiting vocabulary
 * that says nothing about a specific candidate; (3) a requirements-section boost, so
 * terms actually listed as a requirement outweigh terms merely mentioned in passing
 * (company blurb, benefits, etc).
 */
class KeywordMatcher
{
    /**
     * Compared against the accent-stripped, lowercased form of each token (see
     * normalize()) — entries here must already be in that normalized form.
     */
    private const STOPWORDS = [
        'a', 'o', 'as', 'os', 'de', 'da', 'do', 'das', 'dos', 'e', 'ou', 'em', 'no', 'na',
        'nos', 'nas', 'num', 'numa', 'para', 'por', 'com', 'sem', 'sob', 'sobre', 'entre',
        'ate', 'apos', 'ante', 'perante', 'um', 'uma', 'uns', 'umas', 'que', 'se', 'sua',
        'seu', 'suas', 'seus', 'ao', 'aos', 'sao', 'ser', 'estar', 'tem',
        'ter', 'como', 'mais', 'menos', 'muito', 'muita', 'muitos', 'muitas', 'pouco',
        'este', 'esta', 'estes', 'estas', 'esse', 'essa', 'esses', 'essas', 'isso', 'isto',
        'aquele', 'aquela', 'aqueles', 'aquelas', 'tambem', 'nao', 'sim', 'mas', 'porem',
        'quando', 'onde', 'qual', 'quais', 'cada', 'todo', 'toda', 'todos', 'todas',
        'outro', 'outra', 'outros', 'outras', 'pelo', 'pela', 'pelos', 'pelas', 'deste',
        'desta', 'destes', 'destas', 'nesse', 'nessa', 'nesses', 'nessas', 'neste', 'nesta',
        'nestes', 'nestas', 'seja', 'sejam', 'ficar', 'pode', 'podem', 'podera',
        'poderao', 'deve', 'devem', 'devera', 'deverao', 'the', 'and', 'or', 'for', 'with',
        'without', 'from', 'this', 'that', 'these', 'those', 'will', 'shall', 'must',
        'have', 'has', 'are', 'is', 'be', 'been', 'being',
        // Vocabulário genérico de recrutamento/RH: aparece em praticamente qualquer
        // vaga e qualquer CV, independentemente da área — não distingue candidatos.
        'anos', 'ano', 'vaga', 'vagas', 'empresa', 'empresas', 'trabalho', 'trabalhar',
        'candidato', 'candidata', 'candidatos', 'candidatas', 'candidatura', 'candidaturas',
        'funcao', 'funcoes', 'area', 'areas', 'sector', 'setor', 'requisitos', 'requisito',
        'perfil', 'colaborador', 'colaboradores', 'colaboradora', 'colaboradoras', 'equipa',
        'equipe', 'ambiente', 'oportunidade', 'oportunidades', 'local', 'localizacao',
        'horario', 'horarios', 'salario', 'salarios', 'beneficios', 'remuneracao',
        'disponibilidade', 'curriculo', 'envie', 'envio', 'enviar', 'contacto', 'contato',
        'contactos', 'contatos', 'experiencia', 'experiencias', 'conhecimento',
        'conhecimentos', 'procedimento', 'procedimentos', 'sistema', 'sistemas', 'formacao',
        'comunicacao', 'classe', 'acordo', 'obrigatoria', 'obrigatorio', 'necessaria',
        'necessario', 'desejavel', 'minima', 'minimo', 'maxima', 'maximo', 'vantagem',
        'turnos', 'turno', 'disponivel', 'seguranca', 'informacoes', 'informacao',
        'responsavel', 'responsaveis', 'apresentar', 'possuir', 'possui',
        // Vocabulário genérico adicional (adjectivos/substantivos de "cultura de
        // empresa" e processo de candidatura que aparecem em qualquer anúncio).
        'profissional', 'profissionais', 'atividades', 'actividades', 'tarefas',
        'responsabilidades', 'processo', 'selecao', 'seleccao', 'interessados',
        'interessado', 'favor', 'obrigado', 'cumprimentos', 'atenciosamente', 'mercado',
        'cliente', 'clientes', 'servico', 'servicos', 'qualidade', 'excelencia',
        'compromisso', 'valores', 'missao', 'visao', 'cultura', 'flexibilidade',
        'iniciativa', 'proatividade', 'dinamico', 'dinamica', 'dinamismo', 'motivado',
        'motivada', 'motivacao', 'organizado', 'organizada', 'organizacao', 'rigor',
        'etica', 'integridade', 'excelente', 'otimo', 'otima', 'boa', 'bom', 'forte',
        'solido', 'solida', 'diversos', 'diversas', 'varios', 'varias', 'principais',
        'principal', 'geral', 'gerais', 'importante', 'importantes', 'essencial',
        'essenciais', 'fundamental', 'fundamentais', 'capacidade', 'capacidades',
        'habilidade', 'habilidades', 'funcionario', 'funcionarios', 'empregador',
        'emprego', 'empregos', 'interesse', 'apto', 'apta', 'aptos', 'aptas',
        'idade', 'idades', 'sexo', 'genero', 'natural', 'residente', 'residencia',
        'morada', 'nacionalidade',
        // Qualificadores genéricos que costumam preceder um requisito real sem serem
        // eles próprios o requisito ("experiência comprovada", "certificação válida").
        'comprovada', 'comprovado', 'comprovadas', 'comprovados', 'reconhecida',
        'reconhecido', 'reconhecidas', 'reconhecidos', 'valida', 'validas', 'validos',
        'atualizada', 'atualizado', 'atualizadas', 'atualizados', 'actualizada',
        'actualizado', 'actualizadas', 'actualizados', 'relevante', 'relevantes',
        'adequada', 'adequado', 'adequadas', 'adequados', 'preferencialmente',
        'preferencial',
    ];

    /**
     * Acronyms that are administrative/generic (company structure, currency, common
     * document formats) rather than domain requirements — high frequency, low signal.
     */
    private const ACRONYM_BLOCKLIST = [
        'SA', 'LDA', 'RH', 'TI', 'CV', 'PDF', 'CEO', 'CFO', 'CTO', 'COO', 'ERP', 'CRM',
        'KPI', 'ONG', 'IVA', 'INSS', 'USD', 'EUR', 'KZ', 'AO', 'ID', 'WWW', 'HTTP', 'HTTPS',
    ];

    /**
     * Section headings that mark where a job description states its actual
     * requirements — terms found after one of these (until the next heading below,
     * or the end of the text) are boosted relative to the rest of the posting.
     */
    private const REQUIREMENT_HEADINGS = [
        'requisitos', 'requisito', 'perfil pretendido', 'perfil do candidato', 'perfil',
        'qualificacoes', 'qualificacao', 'competencias', 'competencia', 'conhecimentos',
        'formacao exigida', 'experiencia necessaria', 'requirements', 'qualifications',
        'skills', 'o que procuramos', 'habilidades', 'exigencias', 'exige-se',
    ];

    /**
     * Headings that typically follow the requirements section — used to cut off the
     * boosted zone before generic boilerplate (benefits, how to apply) dilutes it.
     */
    private const SECTION_END_HEADINGS = [
        'oferecemos', 'beneficios', 'como se candidatar', 'candidatura', 'enviar cv',
        'contactos', 'sobre a empresa', 'sobre nos', 'o que oferecemos', 'salario',
        'remuneracao', 'horario de trabalho', 'local de trabalho',
        // Anúncios escritos em inglês são comuns em multinacionais e no offshore;
        // sem estes, o que a empresa oferece entrava na lista de requisitos.
        'benefits', 'we offer', 'what we offer', 'how to apply', 'about us',
        'about the company', 'to apply', 'send your cv',
    ];

    private const ACRONYM_WEIGHT = 5.0;
    private const PHRASE_WEIGHT = 4.0;
    private const BIGRAM_WEIGHT = 3.0;
    private const REQUIREMENTS_BOOST = 1.5;

    /** Abaixo disto a "secção de requisitos" detectada é curta de mais para ser fiável. */
    private const MIN_REQUIREMENTS_LENGTH = 10;

    /** Acima disto a linha é texto corrido, não um título de secção. */
    private const MAX_HEADING_LENGTH = 60;
    private const MAX_WORDS = 40;
    private const MAX_BIGRAMS = 20;

    /**
     * Vocabulário de um texto que já é só requisitos (uma linha de requisito, por
     * exemplo), sem tentar descobrir secções dentro dele.
     *
     * @return array<int, array{term: string, weight: float}>
     */
    public static function extractKeywordsFromRequirements(string $text): array
    {
        return self::buildKeywords(
            html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8'),
            ''
        );
    }

    /**
     * @return array<int, array{term: string, weight: float}>
     */
    public static function extractKeywords(string $text): array
    {
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8');

        // O vocabulário sai da secção de requisitos quando ela existe, em vez de
        // sair do anúncio inteiro. O resto do anúncio — quem é a empresa, o convite
        // ("procura um novo desafio"), o email para onde enviar — repete-se em
        // qualquer vaga e só dilui o peso dos termos que distinguem candidatos.
        // Sem secção reconhecível, mantém-se o texto todo.
        $source = self::extractRequirementsSection($text);

        if (mb_strlen(trim($source), 'UTF-8') < self::MIN_REQUIREMENTS_LENGTH) {
            $source = $text;
        }

        return self::buildKeywords($source, self::normalize($source));
    }

    /**
     * @return array<int, array{term: string, weight: float}>
     */
    private static function buildKeywords(string $source, string $normalizedRequirements): array
    {
        $keywords = [];

        $add = function (string $term, float $weight) use (&$keywords, $normalizedRequirements) {
            $key = self::normalize($term);

            if ($key === '') {
                return;
            }

            if ($normalizedRequirements !== '' && str_contains($normalizedRequirements, $key)) {
                $weight *= self::REQUIREMENTS_BOOST;
            }

            if (!isset($keywords[$key]) || $keywords[$key]['weight'] < $weight) {
                $keywords[$key] = ['term' => $term, 'weight' => $weight];
            }
        };

        foreach (self::extractAcronyms($source) as $term) {
            $add($term, self::ACRONYM_WEIGHT);
        }

        foreach (self::extractCapitalizedPhrases($source) as $term) {
            $add($term, self::PHRASE_WEIGHT);
        }

        foreach (self::extractContentBigrams($source) as $term) {
            $add($term, self::BIGRAM_WEIGHT);
        }

        foreach (self::extractSignificantWords($source) as $term => $count) {
            $add($term, self::weightForWordLength(mb_strlen($term, 'UTF-8')));
        }

        return array_values($keywords);
    }

    /**
     * A parte do anúncio que enumera requisitos, para quem precisa dela fora desta
     * classe (o embedding da vaga, por exemplo). Devolve '' se não a reconhecer.
     */
    public static function requirementsSection(string $text): string
    {
        return self::extractRequirementsSection(
            html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Diz se uma linha é apenas um cabeçalho de secção ("Requisitos:", "Perfil
     * pretendido") — útil para não a tratar como se fosse um requisito.
     */
    public static function isRequirementHeading(string $line): bool
    {
        $normalized = self::normalize(rtrim(trim($line), ':'));

        return in_array($normalized, self::REQUIREMENT_HEADINGS, true);
    }

    /**
     * @param array<int, array{term: string, weight: float}> $keywords
     * @return array{score: float, matched: array<int, string>, total: int}|null
     */
    public static function score(array $keywords, ?string $cvText): ?array
    {
        if (empty($keywords) || !$cvText) {
            return null;
        }

        $normalizedCv = self::normalize($cvText);
        $cvStems = self::tokenizeStems($cvText);

        $totalWeight = 0.0;
        $matchedWeight = 0.0;
        $matched = [];

        foreach ($keywords as $keyword) {
            $totalWeight += $keyword['weight'];
            $term = $keyword['term'];

            // Multi-word terms (phrases/bigrams) need adjacency, so they're checked as a
            // literal substring; single words/acronyms are stemmed on both sides so a
            // simple singular/plural mismatch ("certificação" vs "certificações") still
            // counts as a match instead of silently missing.
            $isMatch = str_contains($term, ' ')
                ? str_contains($normalizedCv, self::normalize($term))
                : isset($cvStems[self::stem(self::normalize($term))]);

            if ($isMatch) {
                $matchedWeight += $keyword['weight'];
                $matched[] = $term;
            }
        }

        if ($totalWeight <= 0.0) {
            return null;
        }

        return [
            'score' => $matchedWeight / $totalWeight,
            'matched' => $matched,
            'total' => count($keywords),
        ];
    }

    private const SEMANTIC_WEIGHT = 0.5;
    private const KEYWORD_WEIGHT = 0.5;

    /**
     * Combina a pontuação semântica (embedding) com a das palavras-chave.
     *
     * Era 80/20 a favor da semântica, e isso comprimia os resultados: a semelhança
     * de coseno entre dois documentos longos mede sobretudo "isto é um texto
     * profissional em português", não "esta pessoa cumpre os requisitos". Medido
     * num caso real: um CV de Técnica de Análises Clínicas contra uma vaga de
     * Recursos Humanos deu 0,91 de semelhança — mais alto do que o de uma
     * candidata da área — enquanto as palavras-chave, essas, acertaram em zero.
     * Com 50/50 o sinal que distingue candidatos deixa de ser abafado pelo sinal
     * que não distingue nada.
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

        return (self::SEMANTIC_WEIGHT * $semanticScore) + (self::KEYWORD_WEIGHT * $keywordScore);
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
            if (in_array($match, self::ACRONYM_BLOCKLIST, true)) {
                continue;
            }

            // Require 3+ letters: filters out short administrative abbreviations
            // (SA, RH, TI, CV...) that slip past the blocklist under a different guise
            // while still keeping domain acronyms like BOP, HSE, IADC, IWCF, ISO.
            if (strlen(preg_replace('/[^A-Z]/', '', $match)) >= 3) {
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
        $phrases = [];

        // Linha a linha: uma "frase" que atravessa uma quebra de linha ("Requisitos
        // Experiência", "Excel Diferencial") não existe em nenhum CV, por isso nunca
        // poderia ser encontrada — apenas rouba peso aos termos reais.
        foreach (preg_split('/\R+/u', $text) as $line) {
            preg_match_all('/\b(\p{Lu}\p{Ll}+(?:[ \t]+\p{Lu}\p{Ll}+){1,3})\b/u', $line, $matches);

            foreach ($matches[0] as $match) {
                $phrase = trim($match);

                if ($phrase !== '' && !in_array($phrase, $phrases, true)) {
                    $phrases[] = $phrase;
                }
            }
        }

        return $phrases;
    }

    /**
     * Lowercase multi-word phrases where the content words are specific enough on
     * their own (4+ letters, not a stopword) — catches domain phrases that aren't
     * capitalized, which single-word extraction would break apart and Title Case
     * extraction would never see. Handles both direct adjacency ("controlo poço")
     * and the very common Portuguese "noun + preposition + noun" pattern with a
     * single connector word in between ("controlo DE poço", "trabalho EM altura"),
     * since requiring strict adjacency would silently miss almost every such phrase.
     *
     * @return array<int, string>
     */
    private static function extractContentBigrams(string $text): array
    {
        // Unlike the other extractors, this needs every word — including short
        // connectors ("de", "em", "ao"...) — so adjacency in $words matches adjacency
        // in the real sentence; dropping short words here would silently collapse
        // "controlo DE poço" into "controlo poço" and lose the connector entirely.
        $phrases = [];

        // Também aqui as quebras de linha são fronteiras: numa lista de requisitos,
        // o fim de uma linha e o início da seguinte não formam uma expressão.
        foreach (preg_split('/\R+/u', $text) as $line) {
            self::collectLineBigrams($line, $phrases);
        }

        return array_slice($phrases, 0, self::MAX_BIGRAMS);
    }

    /**
     * @param array<int, string> $phrases
     */
    private static function collectLineBigrams(string $line, array &$phrases): void
    {
        preg_match_all('/\p{L}[\p{L}\p{N}]*/u', mb_strtolower($line, 'UTF-8'), $matches);
        $words = $matches[0];
        $count = count($words);

        for ($i = 0; $i < $count - 1; $i++) {
            $a = $words[$i];

            if (mb_strlen($a, 'UTF-8') < 4 || in_array(self::normalize($a), self::STOPWORDS, true)) {
                continue;
            }

            $b = $words[$i + 1];
            $bIsStopword = in_array(self::normalize($b), self::STOPWORDS, true);

            if (!$bIsStopword && mb_strlen($b, 'UTF-8') >= 4) {
                self::addPhraseCandidate($phrases, $a . ' ' . $b);
                continue;
            }

            if ($bIsStopword && $i + 2 < $count) {
                $c = $words[$i + 2];

                if (mb_strlen($c, 'UTF-8') >= 4 && !in_array(self::normalize($c), self::STOPWORDS, true)) {
                    self::addPhraseCandidate($phrases, $a . ' ' . $b . ' ' . $c);
                }
            }
        }
    }

    private static function addPhraseCandidate(array &$phrases, string $phrase): void
    {
        if (!in_array($phrase, $phrases, true)) {
            $phrases[] = $phrase;
        }
    }

    /**
     * Frequent, non-trivial words from the description — a language-agnostic proxy
     * for "what this posting keeps talking about" when no acronyms/phrases apply.
     *
     * @return array<string, int>
     */
    private static function extractSignificantWords(string $text): array
    {
        preg_match_all('/\p{L}[\p{L}\p{N}]{3,}/u', mb_strtolower($text, 'UTF-8'), $matches);

        $counts = [];

        foreach ($matches[0] as $word) {
            // Stopwords are matched on the accent-stripped form so "não"/"nao",
            // "está"/"esta", "função"/"funcao" etc. are all correctly filtered.
            if (in_array(self::normalize($word), self::STOPWORDS, true)) {
                continue;
            }

            $counts[$word] = ($counts[$word] ?? 0) + 1;
        }

        arsort($counts);

        return array_slice($counts, 0, self::MAX_WORDS, true);
    }

    /**
     * Longer words tend to be more specific/technical in Portuguese ("perfuração",
     * "hidráulico", "certificação" vs "boa", "área", "vaga") — a cheap proxy for
     * term specificity without a real corpus to compute document frequency from.
     */
    private static function weightForWordLength(int $length): float
    {
        if ($length >= 8) {
            return 3.0;
        }

        if ($length >= 6) {
            return 2.0;
        }

        return 1.0;
    }

    /**
     * Finds the text between the first requirements-style heading and the next
     * section-ending heading (or the end of the text) — an approximation of "the part
     * of this posting that actually lists requirements" versus company blurb/benefits.
     * Character-based (mb_*) throughout so offsets found in the lowercased copy stay
     * valid when used to slice the original (accented) text.
     */
    private static function extractRequirementsSection(string $text): string
    {
        $lines = preg_split('/\R/u', $text);
        $start = null;
        $firstLine = null;

        foreach ($lines as $index => $line) {
            $folded = trim(self::foldForSearch($line));

            if (!self::isHeadingLine($folded, self::REQUIREMENT_HEADINGS)) {
                continue;
            }

            $start = $index;

            // "Requisitos: dois anos de experiência" mete o título e o primeiro
            // requisito na mesma linha; o que vem depois dos dois pontos conta.
            $colon = mb_strpos($line, ':', 0, 'UTF-8');

            if ($colon !== false) {
                $rest = trim(mb_substr($line, $colon + 1, null, 'UTF-8'));
                $firstLine = $rest !== '' ? $rest : null;
            }

            break;
        }

        if ($start === null) {
            return '';
        }

        $section = $firstLine === null ? [] : [$firstLine];

        foreach (array_slice($lines, $start + 1) as $line) {
            $folded = trim(self::foldForSearch($line));

            // O que a empresa oferece e como concorrer não são requisitos.
            if (self::startsWithHeading($folded, self::SECTION_END_HEADINGS)) {
                break;
            }

            $section[] = $line;
        }

        return trim(implode("\n", $section));
    }

    /**
     * Uma linha é um título de secção se for curta, não terminar em ponto final e
     * mencionar um dos títulos conhecidos.
     *
     * A regra anterior aceitava o título em qualquer sítio do texto, e por isso uma
     * frase corrida como "Procuramos alguém com o perfil certo para receber os
     * nossos clientes" abria a secção de requisitos a meio de uma frase de
     * marketing — que passava depois a constar da lista de requisitos.
     *
     * @param array<int, string> $headings
     */
    private static function isHeadingLine(string $folded, array $headings): bool
    {
        if ($folded === '' || mb_strlen($folded, 'UTF-8') > self::MAX_HEADING_LENGTH) {
            return false;
        }

        if (str_ends_with($folded, '.')) {
            return false;
        }

        foreach ($headings as $heading) {
            if (str_contains($folded, $heading)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Para fechar a secção basta que a linha comece pelo título ("Benefícios: …",
     * "Oferecemos: …", "Como se candidatar: …"), sem exigir que seja curta.
     *
     * @param array<int, string> $headings
     */
    private static function startsWithHeading(string $folded, array $headings): bool
    {
        foreach ($headings as $heading) {
            if (str_starts_with($folded, $heading)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, true> set of stemmed, normalized words found in the text
     */
    private static function tokenizeStems(string $text): array
    {
        preg_match_all('/\p{L}[\p{L}\p{N}]{2,}/u', mb_strtolower($text, 'UTF-8'), $matches);

        $stems = [];

        foreach ($matches[0] as $word) {
            $stems[self::stem(self::normalize($word))] = true;
        }

        return $stems;
    }

    /**
     * Lightweight Portuguese stemming for the common plural/suffix mismatches that
     * would otherwise make an exact-substring keyword check miss an obvious match
     * (e.g. the job says "certificação", the CV says "certificações"). Deliberately
     * narrow (only applied to already-normalized, accent-stripped words of some
     * minimum length) to avoid mangling short words/acronyms into false matches.
     */
    private static function stem(string $normalized): string
    {
        $length = mb_strlen($normalized, 'UTF-8');

        // "-ções" → "-ção" (accent-stripped: "coes" → "cao"), e.g.
        // certificacoes -> certificacao, qualificacoes -> qualificacao.
        if ($length >= 5 && str_ends_with($normalized, 'coes')) {
            return mb_substr($normalized, 0, -4, 'UTF-8') . 'cao';
        }

        // Generic plural stripping for longer words only, to avoid mangling short
        // words/acronyms (e.g. "BOP", "gás") into unrelated stems.
        if ($length >= 5 && str_ends_with($normalized, 's') && !str_ends_with($normalized, 'ss')) {
            return mb_substr($normalized, 0, -1, 'UTF-8');
        }

        return $normalized;
    }

    private static function normalize(string $text): string
    {
        return trim(preg_replace('/\s+/', ' ', self::foldForSearch($text)));
    }

    /**
     * Minúsculas e sem acentos, carácter a carácter. Ao contrário de normalize(),
     * não mexe nos espaços — por isso as posições encontradas aqui ainda servem
     * para cortar o texto original.
     */
    private static function foldForSearch(string $text): string
    {
        return strtr(mb_strtolower($text, 'UTF-8'), [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n',
        ]);
    }
}

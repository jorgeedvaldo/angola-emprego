<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\Post;
use App\Support\Seo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * O cabeçalho de SEO — o que um plugin como o Yoast escreve num WordPress.
 *
 * Nada disto se vê na página, e é por isso que precisa de testes: quando parte,
 * parte em silêncio e só se dá por ela meses depois, nos resultados de busca.
 */
class SeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Seo::esquecerMedidas();
    }

    private function artigo(string $texto = '<p>Texto do artigo.</p>'): Post
    {
        return Post::create([
            'title' => 'Como escrever um bom currículo',
            'slug' => 'como-escrever-um-bom-curriculo',
            'description' => $texto,
        ]);
    }

    /** @return array<int, array<string, mixed>> os nós do JSON-LD todo da página */
    private function fichas(string $url): array
    {
        $html = $this->get($url)->assertOk()->getContent();

        preg_match_all('#<script type="application/ld\+json"[^>]*>(.*?)</script>#s', $html, $encontrados);

        $nos = [];

        foreach ($encontrados[1] as $bloco) {
            $lido = json_decode($bloco, true);

            $this->assertNotNull($lido, "Há um bloco de JSON-LD inválido em $url: " . json_last_error_msg());

            $nos = array_merge($nos, $lido['@graph'] ?? [$lido]);
        }

        return $nos;
    }

    private function tipos(array $nos): array
    {
        return array_map(fn ($no) => $no['@type'] ?? '?', $nos);
    }

    // ------------------------------------------------------------ canónico

    /**
     * Sem canónico, a mesma página com ?fbclid= do Facebook ou ?gclid= do
     * Google conta como um duplicado e divide a força que devia ter junta.
     */
    public function test_every_page_says_which_address_is_the_real_one()
    {
        $this->get('/sobre?fbclid=IwAR123')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="' . url('/sobre') . '" />', false);
    }

    public function test_a_page_that_declares_no_canonical_points_at_itself()
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="' . url('/login') . '" />', false);
    }

    // -------------------------------------------------------------- robots

    public function test_an_ordinary_page_asks_to_be_indexed()
    {
        $this->get('/vagas')->assertOk()->assertSee(Seo::ROBOTS_INDEXAVEL, false);
    }

    /** Uma pesquisa tem o conteúdo de outra página e não deve ser indexada. */
    public function test_a_search_page_asks_not_to_be_indexed()
    {
        $this->get('/vagas?q=contabilidade')->assertOk()->assertSee(Seo::ROBOTS_ESCONDIDO, false);
    }

    /** Mas a página 2 de uma listagem é conteúdo a sério. */
    public function test_the_second_page_of_a_listing_is_still_indexed()
    {
        $this->get('/vagas?page=2')->assertOk()->assertSee(Seo::ROBOTS_INDEXAVEL, false);
    }

    public function test_the_private_pages_ask_not_to_be_indexed()
    {
        foreach (['/login', '/register'] as $url) {
            $this->get($url)->assertOk()->assertSee(Seo::ROBOTS_ESCONDIDO, false);
        }
    }

    // -------------------------------------------------------------- imagem

    /**
     * O Facebook e o LinkedIn acreditam na medida que lhes dermos. A antiga
     * dizia 1200x630 para toda a gente, incluindo para a imagem por omissão,
     * que é quadrada — e saía cortada.
     */
    public function test_the_share_image_is_measured_and_not_guessed()
    {
        $html = $this->get('/sobre')->assertOk()->getContent();

        [$largura, $altura] = getimagesize(public_path('assets/img/og-default.png'));

        $this->assertStringContainsString('<meta property="og:image:width" content="' . $largura . '"', $html);
        $this->assertStringContainsString('<meta property="og:image:height" content="' . $altura . '"', $html);
    }

    // ------------------------------------------------------------- o grafo

    public function test_the_page_the_site_and_the_organisation_are_linked_to_each_other()
    {
        $nos = collect($this->fichas('/sobre'));
        $site = rtrim(url('/'), '/') . '/';

        $website = $nos->firstWhere('@type', 'WebSite');
        $pagina = $nos->first(fn ($no) => isset($no['isPartOf']));

        $this->assertSame($site . '#organizacao', $website['publisher']['@id']);
        $this->assertSame($site . '#website', $pagina['isPartOf']['@id']);
        $this->assertSame($site . '#organizacao', $pagina['about']['@id']);
        $this->assertSame(url('/sobre'), $pagina['@id']);
    }

    /** O tipo é "WebSite", com S grande. "Website" não existe no schema.org. */
    public function test_the_site_is_a_webSite_with_a_capital_s()
    {
        $this->assertContains('WebSite', $this->tipos($this->fichas('/')));
        $this->assertNotContains('Website', $this->tipos($this->fichas('/')));
    }

    /** A caixa de pesquisa que o Google pode mostrar por baixo do site. */
    public function test_the_site_offers_its_search_box_to_google()
    {
        $nos = collect($this->fichas('/'))->keyBy('@type');

        $this->assertSame(
            url('/vagas') . '?q={search_term_string}',
            $nos['WebSite']['potentialAction'][0]['target']['urlTemplate']
        );
    }

    /**
     * Duas fichas de artigo para o mesmo endereço são duas entidades em
     * conflito. A página do artigo tinha uma NewsArticle e uma BlogPosting.
     */
    public function test_an_article_page_declares_exactly_one_article()
    {
        $tipos = $this->tipos($this->fichas('/noticias/' . $this->artigo()->fresh()->slug));

        $artigos = array_intersect($tipos, ['NewsArticle', 'BlogPosting', 'Article']);

        $this->assertCount(1, $artigos, 'Há mais de uma ficha de artigo: ' . implode(', ', $tipos));
    }

    /** E o mesmo para a página: uma só entidade por endereço. */
    public function test_a_listing_declares_one_page_entity_for_its_address()
    {
        $nos = $this->fichas('/vagas');

        $paginas = array_filter($nos, fn ($no) => ($no['@id'] ?? null) === url('/vagas'));
        $tipos = array_unique($this->tipos($paginas));

        $this->assertSame(['CollectionPage'], array_values($tipos));
    }

    public function test_every_public_page_carries_valid_structured_data()
    {
        $post = $this->artigo();
        $vaga = Job::factory()->create(['title' => 'Técnico de Contabilidade']);

        foreach (['/', '/sobre', '/vagas', '/noticias', '/cursos', '/analisador-de-cv',
                  '/noticias/' . $post->fresh()->slug,
                  '/vagas/' . $vaga->fresh()->slug] as $url) {
            $this->assertNotEmpty($this->fichas($url), "Não há dados estruturados em $url.");
        }
    }

    // ------------------------------------------------------- tempo de leitura

    public function test_an_article_says_how_long_it_takes_to_read()
    {
        $post = $this->artigo('<p>' . str_repeat('palavra ', 450) . '</p>');

        $this->get('/noticias/' . $post->fresh()->slug)
            ->assertOk()
            ->assertSee('Tempo estimado de leitura')
            ->assertSee('3 minutos');
    }

    public function test_a_very_short_article_still_takes_a_minute()
    {
        $this->assertSame(1, Seo::tempoDeLeitura('<p>Duas palavras.</p>'));
    }

    // ------------------------------------------------------------- o título

    /**
     * O título vinha de env('APP_NAME'), que devolve null quando a configuração
     * está em cache — e em produção está. Isso deixava todas as páginas com o
     * título a acabar em " - ".
     */
    public function test_the_title_carries_the_site_name_from_the_configuration()
    {
        config(['app.name' => 'Nome Da Configuração']);

        $this->get('/sobre')
            ->assertOk()
            ->assertSee('<title>Sobre Nós - Nome Da Configuração</title>', false);
    }
}

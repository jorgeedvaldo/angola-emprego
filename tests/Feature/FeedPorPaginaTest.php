<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Job;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * O feed de cada artigo e de cada vaga, no endereço da página com /feed ao fim.
 *
 * O que estes testes guardam é que o XML sai válido — um feed partido não dá
 * erro, simplesmente deixa de ser lido, e ninguém dá por isso.
 */
class FeedPorPaginaTest extends TestCase
{
    use RefreshDatabase;

    private function artigo(string $titulo = 'Como escrever um bom currículo', string $texto = '<p>Texto.</p>'): Post
    {
        return Post::create([
            'title' => $titulo,
            'slug' => \Illuminate\Support\Str::slug($titulo),
            'description' => $texto,
        ]);
    }

    private function xml(string $url): \SimpleXMLElement
    {
        $resposta = $this->get($url)->assertOk();

        $resposta->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');

        $xml = simplexml_load_string($resposta->getContent());
        $this->assertNotFalse($xml, "O feed $url não é XML válido.");

        return $xml;
    }

    // ------------------------------------------------------------- artigos

    public function test_an_article_has_its_own_feed()
    {
        $post = $this->artigo();

        $xml = $this->xml('/noticias/' . $post->slug . '/feed');

        $this->assertSame($post->title, (string) $xml->channel->title);
        $this->assertSame(url('/noticias/' . $post->slug), (string) $xml->channel->link);
        $this->assertSame($post->title, (string) $xml->channel->item[0]->title);
    }

    /** O próprio artigo vem primeiro, e atrás dele os mais recentes. */
    public function test_the_article_itself_comes_first_and_the_others_follow()
    {
        $velho = $this->artigo('Artigo mais velho');
        $novo = $this->artigo('Artigo mais novo');

        $xml = $this->xml('/noticias/' . $velho->slug . '/feed');

        $this->assertCount(2, $xml->channel->item);
        $this->assertSame($velho->title, (string) $xml->channel->item[0]->title);
        $this->assertSame($novo->title, (string) $xml->channel->item[1]->title);
    }

    public function test_the_article_page_points_at_its_feed()
    {
        $post = $this->artigo();

        $this->get('/noticias/' . $post->slug)
            ->assertOk()
            ->assertSee('<link rel="alternate" type="application/rss+xml"', false)
            ->assertSee(url('/noticias/' . $post->slug . '/feed'), false);
    }

    /**
     * O endereço do WordPress acaba em barra. Quem copiar o formato de lá tem
     * de chegar ao mesmo sítio.
     */
    public function test_the_wordpress_shaped_address_with_a_trailing_slash_also_works()
    {
        $post = $this->artigo();

        $this->get('/noticias/' . $post->slug . '/feed/')->assertOk();
    }

    public function test_an_article_that_does_not_exist_has_no_feed()
    {
        $this->get('/noticias/nao-existe/feed')->assertStatus(404);
    }

    // --------------------------------------------------------------- vagas

    public function test_a_job_has_its_own_feed()
    {
        $vaga = Job::factory()->create(['title' => 'Técnico de Contabilidade']);

        $xml = $this->xml('/vagas/' . $vaga->slug . '/feed');

        $this->assertSame($vaga->title, (string) $xml->channel->title);
        $this->assertSame($vaga->title, (string) $xml->channel->item[0]->title);
    }

    /**
     * Numa vaga de empresa, o feed é das vagas dessa empresa — é o que o torna
     * útil a quem quer acompanhar quem contrata.
     */
    public function test_the_feed_of_a_company_job_carries_that_companys_jobs()
    {
        $empresa = Company::factory()->create();
        $nossa = Job::factory()->create(['company_id' => $empresa->id, 'title' => 'Vaga da empresa']);
        Job::factory()->create(['company_id' => $empresa->id, 'title' => 'Outra vaga da empresa']);
        Job::factory()->create(['company_id' => null, 'title' => 'Vaga de outra gente']);

        $xml = $this->xml('/vagas/' . $nossa->slug . '/feed');

        $titulos = [];

        foreach ($xml->channel->item as $item) {
            $titulos[] = (string) $item->title;
        }

        $this->assertSame(['Vaga da empresa', 'Outra vaga da empresa'], $titulos);
    }

    /** Uma vaga que a página não mostra também não tem feed. */
    public function test_a_job_that_is_not_public_has_no_feed()
    {
        $empresa = Company::factory()->create(['approval_status' => 'pending']);
        $vaga = Job::factory()->create(['company_id' => $empresa->id]);

        $this->get('/vagas/' . $vaga->slug)->assertStatus(404);
        $this->get('/vagas/' . $vaga->slug . '/feed')->assertStatus(404);
    }

    public function test_the_job_page_points_at_its_feed()
    {
        $vaga = Job::factory()->create();

        $this->get('/vagas/' . $vaga->slug)
            ->assertOk()
            ->assertSee(url('/vagas/' . $vaga->slug . '/feed'), false);
    }

    // ------------------------------------------------------------ o XML

    /**
     * Um "]]>" no meio do texto fecha o CDATA a meio e parte o XML todo. Vem de
     * graça em qualquer artigo que fale de XML.
     */
    public function test_a_cdata_terminator_inside_the_text_does_not_break_the_feed()
    {
        $post = $this->artigo('Falar de XML', '<p>Escreve-se ]]> para fechar.</p>');

        $xml = $this->xml('/noticias/' . $post->slug . '/feed');

        $this->assertStringContainsString(']]&gt;', (string) $xml->channel->item[0]->children('content', true)->encoded);
    }

    /** Aspas e "&" no título são o outro caminho fácil para partir o XML. */
    public function test_ampersands_and_quotes_in_the_title_do_not_break_the_feed()
    {
        $post = $this->artigo('Salários & "benefícios" <hoje>');

        $xml = $this->xml('/noticias/' . $post->slug . '/feed');

        $this->assertSame('Salários & "benefícios" <hoje>', (string) $xml->channel->item[0]->title);
    }

    /**
     * Dentro de um CDATA o texto vai cru. Escapá-lo também — o que o Blade faz
     * sozinho — faz o leitor mostrar "&quot;" e "&amp;" em vez de aspas e "&".
     */
    public function test_the_text_inside_the_cdata_is_not_escaped_twice()
    {
        $post = $this->artigo('Salários', '<p>Fala de "salários & benefícios".</p>');

        $bruto = $this->get('/noticias/' . $post->slug . '/feed')->assertOk()->getContent();

        $this->assertStringContainsString('<![CDATA[Fala de "salários & benefícios".]]>', $bruto);
        $this->assertStringNotContainsString('&quot;salários &amp;', $bruto);
    }

    /** As datas de um RSS 2.0 são RFC 822, não ISO 8601. */
    public function test_the_dates_are_written_the_way_rss_wants_them()
    {
        $post = $this->artigo();

        $xml = $this->xml('/noticias/' . $post->slug . '/feed');

        foreach ([(string) $xml->channel->lastBuildDate, (string) $xml->channel->item[0]->pubDate] as $data) {
            $this->assertMatchesRegularExpression(
                '/^\w{3}, \d{2} \w{3} \d{4} \d{2}:\d{2}:\d{2} [+-]\d{4}$/',
                $data
            );
        }
    }

    /** O feed diz onde ele próprio está, que é o que um leitor grava. */
    public function test_the_feed_says_where_it_lives()
    {
        $post = $this->artigo();

        $xml = $this->xml('/noticias/' . $post->slug . '/feed');
        $atom = $xml->channel->children('atom', true)->link->attributes();

        $this->assertSame(url('/noticias/' . $post->slug . '/feed'), (string) $atom['href']);
    }
}

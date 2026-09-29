<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A faixa "Siga o Angola Emprego no Google News".
 *
 * O endereço é o que importa: é a página de preferências de fontes do Google,
 * com o nosso domínio. Errar o domínio ali manda as pessoas seguir outro site.
 */
class GoogleNewsTest extends TestCase
{
    use RefreshDatabase;

    private const LIGACAO = 'https://www.google.com/preferences/source?q=angolaemprego.com';

    private function vaga(): Job
    {
        return Job::factory()->create();
    }

    private function artigo(): Post
    {
        return Post::create([
            'title' => 'Como escrever um bom currículo',
            'slug' => 'como-escrever-um-bom-curriculo',
            'description' => '<p>Texto do artigo.</p>',
        ]);
    }

    /** Nas vagas e nos artigos aparece duas vezes: no início e no fim. */
    public function test_the_job_page_carries_it_at_the_start_and_at_the_end()
    {
        $html = $this->get('/vagas/' . $this->vaga()->slug)->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, self::LIGACAO));
    }

    public function test_the_article_page_carries_it_at_the_start_and_at_the_end()
    {
        $html = $this->get('/noticias/' . $this->artigo()->slug)->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, self::LIGACAO));
    }

    public function test_the_home_page_carries_it_once()
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, self::LIGACAO));
    }

    public function test_the_about_page_carries_it_once()
    {
        $html = $this->get('/sobre')->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, self::LIGACAO));
    }

    public function test_it_opens_in_a_new_tab_without_handing_over_the_window()
    {
        $html = $this->get('/sobre')->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/<a href="' . preg_quote(self::LIGACAO, '/') . '"\s+target="_blank" rel="noopener"/',
            $html
        );
    }

    public function test_it_says_what_it_is_in_portuguese()
    {
        $this->get('/sobre')
            ->assertOk()
            ->assertSee('Siga o Angola Emprego no')
            ->assertSee('Seguir')
            ->assertSee('Seguir o Angola Emprego no Google Notícias')
            ->assertSee('class="gnews-news">Notícias<', false);
    }

    public function test_it_follows_the_language_of_the_page()
    {
        $this->get(route('locale.switch', 'en'));

        $this->get('/sobre')
            ->assertOk()
            ->assertSee('Follow Angola Emprego on')
            ->assertSee('class="gnews-news">News<', false)
            ->assertDontSee('Siga o Angola Emprego no');
    }

    /**
     * O bloco de estilos está dentro de um @once. Nas vagas e nos artigos a
     * faixa entra duas vezes e o CSS não pode vir repetido.
     */
    public function test_the_styles_are_written_only_once_even_when_it_appears_twice()
    {
        $html = $this->get('/vagas/' . $this->vaga()->slug)->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, 'class="gnews-cartao"'));
        $this->assertSame(1, substr_count($html, '.gnews-icone {'));
    }
}

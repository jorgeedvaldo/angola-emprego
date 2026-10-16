<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * O que a investigação do esgotamento das 30 ligações encontrou: consultas
 * caras sem cache, numa coluna sem índice, em rotas sem limite de pedidos.
 *
 * Isto não é o sistema de resiliência (que foi removido de propósito). É
 * índice de base de dados e throttle, como o resto do projecto já usa.
 */
class InvestigacaoLigacoesMysqlTest extends TestCase
{
    use RefreshDatabase;

    /** O índice existe e tem o nome que a migração de baixo espera encontrar. */
    public function test_the_jobs_company_column_has_an_index()
    {
        $this->assertTrue(
            collect(Schema::getConnection()->select("PRAGMA index_list('jobs')"))
                ->contains(fn ($indice) => $indice->name === 'jobs_company_index'),
            'A coluna jobs.company devia ter o índice jobs_company_index.'
        );
    }

    /** As rotas que os crawlers mais visitam passam a ter limite de pedidos. */
    public function test_the_sitemap_is_rate_limited()
    {
        for ($i = 0; $i < 21; $i++) {
            $resposta = $this->get('/sitemap.xml');
        }

        $resposta->assertStatus(429);
    }

    public function test_the_general_feed_is_rate_limited()
    {
        for ($i = 0; $i < 31; $i++) {
            $resposta = $this->get('/feed');
        }

        $resposta->assertStatus(429);
    }

    public function test_a_job_feed_is_rate_limited()
    {
        $vaga = Job::factory()->create();

        for ($i = 0; $i < 31; $i++) {
            $resposta = $this->get('/vagas/' . $vaga->slug . '/feed');
        }

        $resposta->assertStatus(429);
    }

    public function test_a_post_feed_is_rate_limited()
    {
        $post = Post::create([
            'title' => 'Artigo',
            'slug' => 'artigo',
            'description' => '<p>Texto.</p>',
        ]);

        for ($i = 0; $i < 31; $i++) {
            $resposta = $this->get('/noticias/' . $post->slug . '/feed');
        }

        $resposta->assertStatus(429);
    }

    /** O limite não pode incomodar quem visita uma vez, como qualquer leitor normal. */
    public function test_a_single_visit_to_each_route_is_never_throttled()
    {
        $vaga = Job::factory()->create();
        Post::create(['title' => 'Artigo', 'slug' => 'artigo-2', 'description' => '<p>Texto.</p>']);

        $this->get('/sitemap.xml')->assertOk();
        $this->get('/feed')->assertOk();
        $this->get('/vagas/' . $vaga->slug . '/feed')->assertOk();
        $this->get('/vagas')->assertOk();
    }
}

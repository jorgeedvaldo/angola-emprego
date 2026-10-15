<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\Post;
use App\Models\User;
use App\Services\CircuitoBaseDeDados;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use PDOException;
use RuntimeException;
use Tests\TestCase;

/**
 * O site visto de fora quando o MySQL recusa ligações.
 *
 * O critério é um só: o visitante continua a conseguir abrir as páginas
 * públicas que tenham conteúdo guardado, e nunca lhe aparece um erro técnico.
 */
class ResilienciaHttpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::store(config('resiliencia.loja'))->flush();
    }

    private function erro1226(): QueryException
    {
        $pdo = new PDOException("SQLSTATE[42000] [1226] User 'yqqnyhrgnt_edv' has exceeded the 'max_user_connections' resource (current value: 30)");
        $pdo->errorInfo = ['42000', 1226, "User 'yqqnyhrgnt_edv' has exceeded the 'max_user_connections' resource (current value: 30)"];

        return new QueryException('select * from `jobs`', [], $pdo);
    }

    /** Uma rota que rebenta como rebentaria a sério, para o erro passar pelo Handler. */
    private function rotaQueFalha(string $caminho, \Throwable $erro, string $metodo = 'get'): void
    {
        Route::middleware('web')->{$metodo}($caminho, fn () => throw $erro);
    }

    private function abrirODisjuntor(): void
    {
        $circuito = app(CircuitoBaseDeDados::class);

        for ($i = 0; $i < config('resiliencia.falhas_para_abrir'); $i++) {
            $circuito->falha();
        }
    }

    // ----------------------------------------------------- funcionamento normal

    public function test_with_the_database_up_the_pages_open_as_always()
    {
        Job::factory()->create(['title' => 'Técnico de Contabilidade']);

        $this->get('/')->assertOk();
        $this->get('/vagas')->assertOk()->assertSee('Técnico de Contabilidade');
    }

    // --------------------------------------------- a página de emergência

    public function test_a_connection_failure_gives_a_calm_503_and_not_a_500()
    {
        $this->rotaQueFalha('/_teste/sem-ligacao', $this->erro1226());

        $this->get('/_teste/sem-ligacao')
            ->assertStatus(503)
            ->assertHeader('Retry-After')
            ->assertSee('Serviço temporariamente indisponível')
            ->assertSee('tente novamente dentro de alguns instantes');
    }

    /** Nem o SQL, nem o nome do utilizador da base de dados, nem o 1226. */
    public function test_the_emergency_page_tells_the_visitor_nothing_technical()
    {
        $this->rotaQueFalha('/_teste/sem-ligacao', $this->erro1226());

        $html = $this->get('/_teste/sem-ligacao')->getContent();

        foreach (['1226', 'max_user_connections', 'yqqnyhrgnt_edv', 'SQLSTATE', 'select * from'] as $segredo) {
            $this->assertStringNotContainsString($segredo, $html, "A página de emergência deixou escapar \"$segredo\".");
        }
    }

    /**
     * O limite da coisa: um erro de programação **não** pode virar "serviço
     * indisponível", ou uma avaria a sério fica meses sem ninguém dar por ela.
     */
    public function test_a_programming_mistake_is_still_a_500()
    {
        $this->rotaQueFalha('/_teste/erro-meu', new RuntimeException('isto é um erro de programação'));

        $resposta = $this->get('/_teste/erro-meu');

        $this->assertSame(500, $resposta->getStatusCode());
        $resposta->assertDontSee('Serviço temporariamente indisponível');
    }

    public function test_an_api_request_gets_the_same_503_in_json()
    {
        $this->rotaQueFalha('/_teste/sem-ligacao', $this->erro1226());

        $this->getJson('/_teste/sem-ligacao')
            ->assertStatus(503)
            ->assertJsonStructure(['message'])
            ->assertHeader('Retry-After');
    }

    // ------------------------------------------------------ nada de falsos sins

    /**
     * Uma candidatura, um registo ou um pagamento que falhem por falta de
     * ligação têm de falhar à vista. Fingir que correram bem era pior do que
     * o erro.
     */
    public function test_a_form_that_hits_a_dead_database_never_pretends_it_worked()
    {
        $this->rotaQueFalha('/_teste/gravar', $this->erro1226(), 'post');

        $resposta = $this->post('/_teste/gravar', ['nome' => 'Ana']);

        $resposta->assertStatus(503);
        $this->assertNotEquals(200, $resposta->getStatusCode());
        $resposta->assertDontSee('sucesso');
    }

    // ------------------------------------------- servir da cópia de emergência

    /**
     * O cenário todo: a vaga está em cache, o MySQL cai, e a página abre na
     * mesma. Para provar que veio da cópia e não de uma consulta nova, a vaga
     * é apagada da base de dados antes do segundo pedido.
     */
    public function test_a_job_page_still_opens_from_the_emergency_copy()
    {
        $vaga = Job::factory()->create(['title' => 'Técnico de Contabilidade']);
        $slug = $vaga->slug;

        $this->get('/vagas/' . $slug)->assertOk()->assertSee('Técnico de Contabilidade');

        // A cópia normal expira, a de emergência fica, e o MySQL cai.
        app(\App\Services\DatabaseFallbackCache::class)->esquecerANormal('job_' . $slug);
        Job::query()->delete();
        $this->abrirODisjuntor();

        $this->get('/vagas/' . $slug)
            ->assertOk()
            ->assertSee('Técnico de Contabilidade');
    }

    public function test_the_home_page_still_opens_from_the_emergency_copy()
    {
        Job::factory()->create(['title' => 'Vaga de ontem']);

        $this->get('/')->assertOk()->assertSee('Vaga de ontem');

        app(\App\Services\DatabaseFallbackCache::class)->esquecerANormal('latest_jobs_50');
        Job::query()->delete();
        $this->abrirODisjuntor();

        $this->get('/')->assertOk()->assertSee('Vaga de ontem');
    }

    // ---------------------------------------------------------- recuperação

    public function test_when_the_database_comes_back_the_pages_update_themselves()
    {
        Job::factory()->create(['title' => 'Vaga de ontem']);
        $this->get('/vagas')->assertOk()->assertSee('Vaga de ontem');

        $this->abrirODisjuntor();
        Job::factory()->create(['title' => 'Vaga de hoje']);

        // Com o disjuntor aberto, ainda se vê o de ontem.
        $this->get('/vagas')->assertOk()->assertSee('Vaga de ontem');

        // O MySQL recupera: passado o descanso, as consultas voltam sozinhas.
        $this->travel(config('resiliencia.descanso') + 1)->seconds();
        Cache::store(config('resiliencia.loja'))->forget('fb:latest_jobs_50');

        $this->get('/vagas')->assertOk()->assertSee('Vaga de hoje');
    }

    // ------------------------------------------------------ cache de HTML

    private function ligarACacheDeHtml(): void
    {
        config(['resiliencia.html.activa' => true]);
    }

    public function test_the_second_visitor_gets_the_page_from_the_cache()
    {
        $this->ligarACacheDeHtml();
        Post::create(['title' => 'Artigo', 'slug' => 'artigo', 'description' => '<p>Texto.</p>']);

        $this->get('/noticias')->assertOk()->assertHeaderMissing('X-Cache-Angola');
        $this->get('/noticias')->assertOk()->assertHeader('X-Cache-Angola', 'hit');
    }

    /**
     * Um HEAD — o que os monitores de uptime mandam, e o que o `curl -I` faz —
     * também é servido da cópia. Deixá-lo de fora era deixar de fora quem mais
     * bate à porta.
     */
    public function test_a_head_request_is_served_from_the_cache_too()
    {
        $this->ligarACacheDeHtml();

        $this->get('/sobre')->assertOk();

        $this->head('/sobre')
            ->assertOk()
            ->assertHeader('X-Cache-Angola', 'hit');
    }

    /** Mas um HEAD não enche a cache: a resposta a um HEAD vai sem corpo. */
    public function test_a_head_request_never_fills_the_cache_with_an_empty_page()
    {
        $this->ligarACacheDeHtml();

        $this->head('/sobre')->assertOk();

        $this->get('/sobre')
            ->assertOk()
            ->assertHeaderMissing('X-Cache-Angola')
            ->assertSee('Sobre');
    }

    /**
     * O token CSRF do primeiro visitante não pode ficar na cópia: ou os
     * formulários dos outros rebentavam com 419, ou — pior — passavam todos a
     * partilhar o mesmo token.
     */
    public function test_each_visitor_gets_their_own_csrf_token_from_the_cached_page()
    {
        $this->ligarACacheDeHtml();

        $primeiro = $this->get('/sobre')->assertOk()->getContent();
        $this->flushSession();
        $segundo = $this->get('/sobre')->assertOk()->assertHeader('X-Cache-Angola', 'hit')->getContent();

        preg_match('/name="csrf-token" content="([^"]+)"/', $primeiro, $um);
        preg_match('/name="csrf-token" content="([^"]+)"/', $segundo, $dois);

        $this->assertNotEmpty($um[1] ?? '');
        $this->assertNotEmpty($dois[1] ?? '');
        $this->assertNotSame($um[1], $dois[1], 'A cópia estava a distribuir o token do primeiro visitante.');
        $this->assertStringNotContainsString('@@csrf-token@@', $segundo);
    }

    /** Quem tem sessão iniciada vê a página dele, nunca a de um estranho. */
    public function test_a_signed_in_visitor_is_never_served_the_shared_copy()
    {
        $this->ligarACacheDeHtml();

        $this->get('/sobre')->assertOk();
        $this->get('/sobre')->assertOk()->assertHeader('X-Cache-Angola', 'hit');

        $this->actingAs(User::factory()->create())
            ->get('/sobre')
            ->assertOk()
            ->assertHeaderMissing('X-Cache-Angola');
    }

    /** E a página de quem tem sessão iniciada também não vai para a cache. */
    public function test_the_page_of_a_signed_in_visitor_never_enters_the_cache()
    {
        $this->ligarACacheDeHtml();

        $this->actingAs(User::factory()->create())->get('/sobre')->assertOk();

        $this->flushSession();

        $this->get('/sobre')->assertOk()->assertHeaderMissing('X-Cache-Angola');
    }

    /** Pesquisas e filtros são infinitos: não entram. */
    public function test_searches_and_filters_do_not_enter_the_cache()
    {
        $this->ligarACacheDeHtml();

        $this->get('/vagas?q=contabilidade')->assertOk()->assertHeaderMissing('X-Cache-Angola');
        $this->get('/vagas?q=contabilidade')->assertOk()->assertHeaderMissing('X-Cache-Angola');
    }

    public function test_the_private_pages_do_not_enter_the_cache()
    {
        $this->ligarACacheDeHtml();

        $this->get('/login')->assertOk();
        $this->get('/login')->assertOk()->assertHeaderMissing('X-Cache-Angola');
    }

    /** Uma vaga nova deita fora a cache de HTML: ninguém fica a ver o site de ontem. */
    public function test_publishing_something_new_throws_the_html_cache_away()
    {
        $this->ligarACacheDeHtml();

        $this->get('/vagas')->assertOk();
        $this->get('/vagas')->assertOk()->assertHeader('X-Cache-Angola', 'hit');

        Job::factory()->create(['title' => 'Vaga acabada de sair']);

        $this->get('/vagas')
            ->assertOk()
            ->assertHeaderMissing('X-Cache-Angola')
            ->assertSee('Vaga acabada de sair');
    }

    /** O site fala duas línguas na mesma morada; as cópias não se podem misturar. */
    public function test_the_two_languages_do_not_share_one_copy()
    {
        $this->ligarACacheDeHtml();

        $this->get('/sobre')->assertOk()->assertSee('Sobre');

        $this->get(route('locale.switch', 'en'));

        $this->get('/sobre')->assertOk()->assertHeaderMissing('X-Cache-Angola');
    }
}

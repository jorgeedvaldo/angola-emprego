<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Job;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * O anúncio da análise de candidatos, no fim das vagas e dos artigos.
 *
 * São as duas páginas onde alguém de uma empresa é mais provável que caia
 * vindo do Google — a ler uma vaga da concorrência ou um artigo sobre
 * recrutamento.
 */
class PromoEmpresasTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_the_job_page_ends_with_the_announcement()
    {
        $this->get('/vagas/' . $this->vaga()->slug)
            ->assertOk()
            ->assertSee('promo-empresas', false)
            ->assertSee('Análise de candidatos por inteligência artificial')
            ->assertSee('Serviço gratuito para empresas');
    }

    public function test_the_article_page_ends_with_the_announcement()
    {
        $this->get('/noticias/' . $this->artigo()->slug)
            ->assertOk()
            ->assertSee('promo-empresas', false)
            ->assertSee('Análise de candidatos por inteligência artificial');
    }

    public function test_it_carries_the_three_selling_points_and_both_buttons()
    {
        $resposta = $this->get('/vagas/' . $this->vaga()->slug)->assertOk();

        foreach (['Cadastro grátis', 'Sem mensalidade', 'Leitura automática',
                  'de cada currículo', 'Ranking', 'por compatibilidade'] as $ponto) {
            $resposta->assertSee($ponto);
        }

        $resposta->assertSee(route('register.company'), false)
            ->assertSee(route('cv-analysis.info'), false)
            ->assertSee('Ver como funciona');
    }

    /**
     * Quem já tem conta de empresa não precisa que lhe ofereçam o cadastro; o
     * anúncio só lhe roubava espaço à página.
     */
    public function test_a_company_that_is_already_registered_does_not_see_it()
    {
        $empresa = Company::factory()->create();

        $this->actingAs($empresa->user)
            ->get('/vagas/' . $this->vaga()->slug)
            ->assertOk()
            ->assertDontSee('promo-empresas', false);
    }

    /** Um candidato com conta continua a ver — pode ser ele a querer contratar. */
    public function test_a_candidate_with_an_account_still_sees_it()
    {
        $this->actingAs(User::factory()->create())
            ->get('/vagas/' . $this->vaga()->slug)
            ->assertOk()
            ->assertSee('promo-empresas', false);
    }

    public function test_it_follows_the_language_of_the_page()
    {
        $vaga = $this->vaga();

        $this->get(route('locale.switch', 'en'));

        $this->get('/vagas/' . $vaga->slug)
            ->assertOk()
            ->assertSee('Candidate screening by artificial intelligence')
            ->assertSee('Free for companies')
            ->assertDontSee('Serviço gratuito para empresas');
    }

    /**
     * O bloco de estilos está dentro de um @once. Se um dia a mesma página
     * incluir o anúncio duas vezes, o CSS não pode vir repetido.
     */
    public function test_the_styles_are_written_only_once()
    {
        $html = $this->get('/vagas/' . $this->vaga()->slug)->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '.promo-empresas-caixa {'));
    }
}

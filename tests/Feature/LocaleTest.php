<?php

namespace Tests\Feature;

use App\Http\Middleware\SetLocale;
use App\Models\Company;
use App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_site_is_in_portuguese_by_default()
    {
        $this->get(route('recruiters.index'))
            ->assertOk()
            ->assertSee('Empresas e Recrutadores')
            ->assertSee('Analisar CV');
    }

    public function test_choosing_english_translates_the_page()
    {
        $this->get(route('locale.switch', 'en'))->assertRedirect();

        $this->get(route('recruiters.index'))
            ->assertOk()
            ->assertSee('Companies &amp; Recruiters', false)
            ->assertSee('Analyse CVs')
            ->assertDontSee('Empresas e Recrutadores');
    }

    public function test_the_choice_survives_from_page_to_page()
    {
        $this->get(route('locale.switch', 'en'));

        $this->get(route('cv-analyzer.index'))
            ->assertOk()
            ->assertSee('CV Analyser')
            ->assertSee('The CVs are not stored.')
            ->assertDontSee('Os CVs não são guardados.');

        // E volta atrás quando se escolhe português.
        $this->get(route('locale.switch', 'pt'));
        $this->get(route('cv-analyzer.index'))
            ->assertOk()
            ->assertSee('Os CVs não são guardados.');
    }

    public function test_the_html_lang_attribute_follows_the_choice()
    {
        $this->get(route('recruiters.index'))->assertSee('<html lang="pt-AO">', false);

        $this->get(route('locale.switch', 'en'));
        $this->get(route('recruiters.index'))->assertSee('<html lang="en">', false);
    }

    public function test_an_unknown_language_is_refused()
    {
        $this->get('/idioma/de')->assertNotFound();
        $this->get('/idioma/../etc')->assertNotFound();

        // E o idioma anterior fica intacto.
        $this->get(route('recruiters.index'))->assertSee('Empresas e Recrutadores');
    }

    public function test_the_switcher_is_in_the_footer_of_every_page()
    {
        $resposta = $this->get(route('recruiters.index'));

        $resposta->assertSee('language-switcher', false)
            ->assertSee(route('locale.switch', 'en'), false)
            // O idioma actual aparece, mas não como ligação.
            ->assertSee('Português')
            ->assertSee('English');
    }

    /**
     * Detectar o idioma do browser seria a escolha óbvia, e é de propósito que
     * não se faz: em Angola é muito comum o telemóvel estar configurado em
     * inglês, e o site passaria a abrir em inglês para quem o quer em português.
     */
    public function test_the_browser_language_does_not_change_the_site()
    {
        $this->withHeader('Accept-Language', 'en-GB,en;q=0.9')
            ->get(route('recruiters.index'))
            ->assertOk()
            ->assertSee('Empresas e Recrutadores')
            ->assertDontSee('Analyse CVs');
    }

    public function test_one_visitor_choosing_english_does_not_change_it_for_everyone()
    {
        $this->get(route('locale.switch', 'en'));
        $this->get(route('recruiters.index'))->assertSee('Analyse CVs');

        // Outro visitante, sessão limpa: continua a ver português.
        $this->flushSession();
        $this->get(route('recruiters.index'))->assertSee('Empresas e Recrutadores');
    }

    public function test_the_home_page_translates()
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Somos o maior portal de empregos em Angola')
            ->assertSee('Pesquisar Vagas')
            ->assertSee('Últimas Notícias')
            ->assertSee('Junte-se à nossa Comunidade!');

        $this->get(route('locale.switch', 'en'));

        $this->get('/')
            ->assertOk()
            ->assertSee('The largest jobs portal in Angola')
            ->assertSee('Search jobs')
            ->assertSee('Latest news')
            ->assertSee('Join our community!')
            ->assertDontSee('Últimas Notícias');
    }

    public function test_the_jobs_listing_translates()
    {
        $this->get('/vagas')
            ->assertOk()
            ->assertSee('Explore as melhores oportunidades de carreira em Angola.')
            ->assertSee('Filtrar Vagas')
            ->assertSee('Principais Empresas');

        $this->get(route('locale.switch', 'en'));

        $this->get('/vagas')
            ->assertOk()
            ->assertSee('Explore the best career opportunities in Angola.')
            ->assertSee('Filter jobs')
            ->assertSee('Top employers')
            ->assertDontSee('Filtrar Vagas');
    }

    public function test_the_job_detail_page_translates()
    {
        $vaga = Job::factory()->create(['title' => 'Técnico de Recursos Humanos']);

        $this->get('/vagas/' . $vaga->slug)
            ->assertOk()
            ->assertSee('Descrição da Vaga')
            ->assertSee('Candidatar-se')
            ->assertSee('Vagas Recentes')
            ->assertSee('Partilhar:');

        $this->get(route('locale.switch', 'en'));

        $this->get('/vagas/' . $vaga->slug)
            ->assertOk()
            ->assertSee('Job description')
            ->assertSee('Apply')
            ->assertSee('Recent jobs')
            ->assertSee('Share:')
            ->assertDontSee('Descrição da Vaga')
            // O título da vaga é conteúdo do empregador: fica como foi escrito.
            ->assertSee('Técnico de Recursos Humanos');
    }

    /**
     * O texto dos anexos muda com o número permitido, e é o único sítio destas
     * páginas onde o plural depende de um valor — em português e em inglês a
     * regra é a mesma, mas é onde uma tradução se parte primeiro.
     */
    public function test_the_attachment_help_text_agrees_in_number()
    {
        $empresa = Company::factory()->create([
            'approval_status' => 'approved',
            'max_attachments' => 1,
        ]);
        $empresa->user->markEmailAsVerified();

        $vaga = Job::factory()->create(['company_id' => $empresa->id]);

        $this->get('/vagas/' . $vaga->slug)
            ->assertOk()
            ->assertSee('anexe até <strong>1</strong> ficheiro (', false)
            ->assertDontSee('1</strong> ficheiros');

        $empresa->update(['max_attachments' => 3]);
        \Illuminate\Support\Facades\Cache::flush();

        $this->get('/vagas/' . $vaga->slug)
            ->assertOk()
            ->assertSee('anexe até <strong>3</strong> ficheiros (', false);

        $this->get(route('locale.switch', 'en'));

        $this->get('/vagas/' . $vaga->slug)
            ->assertOk()
            ->assertSee('attach up to <strong>3</strong> files (', false);
    }

    /**
     * Uma chave em falta rende como o próprio nome da chave ("site.home.novo"),
     * que passa despercebido numa página cheia. Nas páginas que já traduzimos,
     * isso é um erro.
     */
    public function test_no_translation_key_leaks_into_the_page()
    {
        foreach (['pt', 'en'] as $idioma) {
            $this->get(route('locale.switch', $idioma));

            $paginas = ['/', '/vagas', route('recruiters.index'), route('cv-analyzer.index')];
            $paginas[] = '/vagas/' . Job::factory()->create()->slug;

            foreach ($paginas as $pagina) {
                $this->get($pagina)
                    ->assertOk()
                    ->assertDontSee('site.home.')
                    ->assertDontSee('site.vagas.')
                    ->assertDontSee('site.nav.')
                    ->assertDontSee('site.footer.')
                    ->assertDontSee('site.analisador.')
                    ->assertDontSee('site.vaga.')
                    ->assertDontSee('site.recrutadores.');
            }
        }
    }

    public function test_every_key_exists_in_both_languages()
    {
        $pt = $this->achatar(require lang_path('pt/site.php'));
        $en = $this->achatar(require lang_path('en/site.php'));

        $this->assertSame([], array_diff($pt, $en), 'Chaves que só existem em português.');
        $this->assertSame([], array_diff($en, $pt), 'Chaves que só existem em inglês.');
        $this->assertSame(array_keys(SetLocale::suportados()), ['pt', 'en']);
    }

    /**
     * @return array<int, string>
     */
    private function achatar(array $traducoes, string $prefixo = ''): array
    {
        $chaves = [];

        foreach ($traducoes as $chave => $valor) {
            $completa = $prefixo ? "$prefixo.$chave" : $chave;

            if (is_array($valor)) {
                $chaves = array_merge($chaves, $this->achatar($valor, $completa));

                continue;
            }

            $chaves[] = $completa;
        }

        return $chaves;
    }
}

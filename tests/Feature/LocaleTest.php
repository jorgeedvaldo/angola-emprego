<?php

namespace Tests\Feature;

use App\Http\Middleware\SetLocale;
use App\Models\Company;
use App\Models\Course;
use App\Models\Post;
use App\Models\User;
use App\Models\Job;
use App\Models\JobApplication;
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

    public function test_the_sign_in_and_sign_up_pages_translate()
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Bem-vindo de volta! Entre para continuar.')
            ->assertSee('Esqueceu a senha?');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Junte-se à nossa comunidade de profissionais.')
            ->assertSee('Nome Completo');

        $this->get(route('locale.switch', 'en'));

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Welcome back! Sign in to carry on.')
            ->assertSee('Forgot your password?')
            ->assertDontSee('Bem-vindo de volta');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Join our community of professionals.')
            ->assertSee('Full name')
            ->assertDontSee('Nome Completo');
    }

    public function test_the_company_and_password_pages_translate()
    {
        $this->get(route('register.company'))
            ->assertOk()
            ->assertSee('Identidade visual da página')
            ->assertSee('Foto de capa');

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Voltar ao login');

        $this->get(route('locale.switch', 'en'));

        $this->get(route('register.company'))
            ->assertOk()
            ->assertSee('Look of your page')
            ->assertSee('Cover photo')
            ->assertDontSee('Foto de capa');

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Back to sign in')
            ->assertDontSee('Voltar ao login');
    }

    /**
     * Os erros de validação vêm do Laravel, não das nossas traduções. Com o
     * idioma por omissão a passar para 'pt' era preciso lang/pt/validation.php,
     * sem o qual o formulário respondia em inglês a quem o preencheu em
     * português.
     */
    public function test_validation_errors_come_back_in_portuguese()
    {
        $this->from(route('register'))
            ->post(route('register'), ['email' => 'nao-e-um-email'])
            ->assertSessionHasErrors(['email' => 'O campo email deve ser um email válido.']);
    }

    public function test_the_company_dashboard_translates()
    {
        $empresa = $this->empresaAprovada();

        $this->actingAs($empresa->user)->get(route('company.dashboard'))
            ->assertOk()
            ->assertSee('Painel da Empresa')
            ->assertSee('Candidaturas recebidas')
            ->assertSee('Guardar página')
            ->assertSee('As suas vagas');

        $this->get(route('locale.switch', 'en'));

        $this->actingAs($empresa->user)->get(route('company.dashboard'))
            ->assertOk()
            ->assertSee('Company dashboard')
            ->assertSee('Applications received')
            ->assertSee('Save page')
            ->assertSee('Your jobs')
            ->assertDontSee('Painel da Empresa');
    }

    public function test_the_job_form_and_applications_pages_translate()
    {
        $empresa = $this->empresaAprovada();
        $vaga = Job::factory()->create(['company_id' => $empresa->id]);

        $this->actingAs($empresa->user)->get(route('company.jobs.create'))
            ->assertOk()
            ->assertSee('Título da vaga')
            ->assertSee('Cancelar');

        $this->actingAs($empresa->user)->get(route('company.jobs.applications', $vaga))
            ->assertOk()
            ->assertSee('Análise de CVs por IA')
            ->assertSee('Ainda não há candidaturas para esta vaga.');

        $this->get(route('locale.switch', 'en'));

        $this->actingAs($empresa->user)->get(route('company.jobs.create'))
            ->assertOk()
            ->assertSee('Job title')
            ->assertSee('Cancel')
            ->assertDontSee('Título da vaga');

        $this->actingAs($empresa->user)->get(route('company.jobs.applications', $vaga))
            ->assertOk()
            ->assertSee('CV analysis with AI')
            ->assertSee('No applications for this job yet.');
    }

    /**
     * O texto que o JavaScript do painel mostra vem do Blade, porque um ficheiro
     * .js não lê os ficheiros de tradução. Se essa ponte se partir, a página
     * traduz e as mensagens de progresso ficam na língua errada.
     */
    public function test_the_panel_javascript_receives_translated_strings()
    {
        $empresa = $this->empresaAprovada();
        $vaga = Job::factory()->create(['company_id' => $empresa->id]);
        // Sem factory para candidaturas, e o bloco do JavaScript só é rendido
        // quando existe pelo menos uma.
        JobApplication::create([
            'job_id' => $vaga->id,
            'name' => 'Ana Nzuzi',
            'email' => 'ana@exemplo.co.ao',
            'subject' => 'Candidatura',
            'message' => 'Boa tarde.',
            'status' => 'new',
            'attachment_path' => 'job-applications/cv.pdf',
            'attachment_name' => 'cv.pdf',
        ]);

        $this->actingAs($empresa->user)->get(route('company.jobs.applications', $vaga))
            ->assertOk()
            ->assertSee('A analisar a descri', false);

        $this->get(route('locale.switch', 'en'));

        $this->actingAs($empresa->user)->get(route('company.jobs.applications', $vaga))
            ->assertOk()
            ->assertSee('Analysing the job description', false)
            ->assertDontSee('A analisar a descri', false);
    }

    private function empresaAprovada(): Company
    {
        $empresa = Company::factory()->create(['approval_status' => 'approved']);
        $empresa->user->markEmailAsVerified();

        return $empresa;
    }

    public function test_the_courses_pages_translate()
    {
        $curso = Course::create([
            'title' => 'Excel para Recursos Humanos',
            'slug' => 'excel-para-recursos-humanos',
            'description' => 'Curso prático de Excel.',
            'is_published' => true,
        ]);

        $this->get(route('courses.index'))
            ->assertOk()
            ->assertSee('Desenvolva as suas competências')
            ->assertSee('Começar agora');

        $this->get(route('courses.show', $curso->slug))
            ->assertOk()
            ->assertSee('Conteúdo do Curso')
            ->assertSee('O seu Progresso');

        $this->get(route('locale.switch', 'en'));

        $this->get(route('courses.index'))
            ->assertOk()
            ->assertSee('Build your skills')
            ->assertSee('Start now')
            ->assertDontSee('Começar agora');

        $this->get(route('courses.show', $curso->slug))
            ->assertOk()
            ->assertSee('Course content')
            ->assertSee('Your progress')
            // O título do curso é conteúdo, fica como foi escrito.
            ->assertSee('Excel para Recursos Humanos');
    }

    public function test_the_news_pages_translate()
    {
        // O slug é regerado no evento created, mas a coluna não aceita nulo.
        $artigo = Post::create([
            'title' => 'Mercado de trabalho em Luanda',
            'slug' => 'mercado-de-trabalho-em-luanda',
            'description' => 'Um artigo sobre o mercado.',
        ]);

        $this->get('/noticias')
            ->assertOk()
            ->assertSee('Notícias, dicas de carreira e atualizações do mercado.')
            ->assertSee('Ler mais');

        $this->get('/noticias/' . $artigo->fresh()->slug)
            ->assertOk()
            ->assertSee('Artigos Recentes');

        $this->get(route('locale.switch', 'en'));

        $this->get('/noticias')
            ->assertOk()
            ->assertSee('News, career advice and market updates.')
            ->assertSee('Read more')
            ->assertDontSee('Ler mais');

        $this->get('/noticias/' . $artigo->fresh()->slug)
            ->assertOk()
            ->assertSee('Recent articles')
            ->assertSee('Mercado de trabalho em Luanda');
    }

    /**
     * O certificado fica em português de propósito, nos dois idiomas: é um
     * documento formal emitido em Angola, e o título "CERTIFICADO DE CONCLUSÃO"
     * está impresso na imagem de fundo — traduzir só o texto sobreposto daria um
     * documento com o corpo numa língua e o título noutra.
     */
    public function test_the_certificate_stays_in_portuguese()
    {
        $this->assertStringContainsString(
            'Este certificado é concedido a',
            file_get_contents(resource_path('views/courses/certificate.blade.php'))
        );
    }

    public function test_the_company_listing_translates()
    {
        $empresa = $this->empresaAprovada();
        Job::factory()->create(['company_id' => $empresa->id]);

        $this->get(route('companies.index'))
            ->assertOk()
            ->assertSee('Páginas oficiais de empresas')
            ->assertSee('Registar empresa')
            // Uma vaga só: singular.
            ->assertSee('1 vaga')
            ->assertDontSee('1 vagas');

        $this->get(route('locale.switch', 'en'));

        $this->get(route('companies.index'))
            ->assertOk()
            ->assertSee('Official pages of companies')
            ->assertSee('Register a company')
            ->assertSee('1 job')
            ->assertDontSee('1 jobs');
    }

    /**
     * A página pública de cada empresa usa um layout próprio, com o seu cabeçalho
     * e rodapé — que também tem de acompanhar o idioma.
     */
    public function test_the_public_company_page_translates()
    {
        $empresa = $this->empresaAprovada();

        $this->get(url('/company/' . $empresa->slug))
            ->assertOk()
            ->assertSee('Vagas abertas')
            ->assertSee('Contactos')
            ->assertSee('Todos os direitos reservados')
            ->assertSee('<html lang="pt-AO">', false);

        $this->get(route('locale.switch', 'en'));

        $this->get(url('/company/' . $empresa->slug))
            ->assertOk()
            ->assertSee('Open positions')
            ->assertSee('Contact')
            ->assertSee('All rights reserved')
            ->assertSee('<html lang="en">', false)
            ->assertDontSee('Vagas abertas');
    }

    public function test_a_job_on_the_company_page_translates()
    {
        $empresa = $this->empresaAprovada();
        $vaga = Job::factory()->create(['company_id' => $empresa->id]);

        $this->get(url('/company/' . $empresa->slug . '/vagas/' . $vaga->slug))
            ->assertOk()
            ->assertSee('Descrição da vaga')
            ->assertSee('Enviar candidatura');

        $this->get(route('locale.switch', 'en'));

        $this->get(url('/company/' . $empresa->slug . '/vagas/' . $vaga->slug))
            ->assertOk()
            ->assertSee('Job description')
            ->assertSee('Send your application')
            ->assertDontSee('Descrição da vaga');
    }

    public function test_the_profile_page_translates()
    {
        $utilizador = User::factory()->create();

        $this->actingAs($utilizador)->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Dados Pessoais')
            ->assertSee('Os Meus Currículos')
            ->assertSee('Formação Académica')
            ->assertSee('Guardar Alterações');

        $this->get(route('locale.switch', 'en'));

        $this->actingAs($utilizador)->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Personal details')
            ->assertSee('My CVs')
            ->assertSee('Education')
            ->assertSee('Save changes')
            ->assertDontSee('Formação Académica');
    }

    public function test_the_public_profile_translates()
    {
        $utilizador = User::factory()->create(['username' => 'ana.nzuzi']);

        $this->get('/@' . $utilizador->username)
            ->assertOk()
            ->assertSee('Código QR')
            ->assertSee('Voltar ao Início');

        $this->get(route('locale.switch', 'en'));

        $this->get('/@' . $utilizador->username)
            ->assertOk()
            ->assertSee('QR code')
            ->assertSee('Back to home')
            ->assertDontSee('Voltar ao Início');
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
            $paginas[] = route('login');
            $paginas[] = route('register');
            $paginas[] = route('register.company');
            $paginas[] = route('password.request');
            $paginas[] = route('courses.index');
            $paginas[] = '/noticias';
            $paginas[] = route('companies.index');
            $paginas[] = route('sobre');
            $paginas[] = route('cv-analysis.info');
            $paginas[] = '/atm-com-dinheiro';

            foreach ($paginas as $pagina) {
                $this->get($pagina)
                    ->assertOk()
                    ->assertDontSee('site.home.')
                    ->assertDontSee('site.vagas.')
                    ->assertDontSee('site.nav.')
                    ->assertDontSee('site.footer.')
                    ->assertDontSee('site.analisador.')
                    ->assertDontSee('site.vaga.')
                    ->assertDontSee('site.auth.')
                    ->assertDontSee('site.painel.')
                    ->assertDontSee('site.vaga_form.')
                    ->assertDontSee('site.candidaturas.')
                    ->assertDontSee('site.cursos.')
                    ->assertDontSee('site.noticias.')
                    ->assertDontSee('site.empresas.')
                    ->assertDontSee('site.perfil.')
                    ->assertDontSee('site.recrutadores.')
                    ->assertDontSee('site.sobre.')
                    ->assertDontSee('site.ia_info.')
                    ->assertDontSee('site.atm.');
            }
        }
    }

    public function test_the_about_page_follows_the_chosen_language()
    {
        $this->get(route('sobre'))
            ->assertOk()
            ->assertSee('Nossa História')
            ->assertSee('Perguntas Frequentes');

        $this->get(route('locale.switch', 'en'));

        $this->get(route('sobre'))
            ->assertOk()
            ->assertSee('Our story')
            ->assertSee('Frequently asked questions')
            ->assertDontSee('Nossa História');
    }

    public function test_the_cv_analysis_explainer_follows_the_chosen_language()
    {
        $this->get(route('cv-analysis.info'))
            ->assertOk()
            ->assertSee('Passo a passo');

        $this->get(route('locale.switch', 'en'));

        $this->get(route('cv-analysis.info'))
            ->assertOk()
            ->assertSee('Step by step')
            ->assertDontSee('Passo a passo');
    }

    /**
     * O localizador de ATMs monta os cartões no browser: as frases que o script
     * usa viajam com a página, por isso têm de mudar de idioma como o resto.
     */
    public function test_the_atm_page_hands_its_javascript_the_chosen_language()
    {
        $this->get('/atm-com-dinheiro')
            ->assertOk()
            ->assertSee('Localizador de ATMs')
            ->assertSee('ATMs encontrados', false);

        $this->get(route('locale.switch', 'en'));

        $this->get('/atm-com-dinheiro')
            ->assertOk()
            ->assertSee('ATM finder')
            ->assertSee('ATMs found', false)
            ->assertDontSee('Localizador de ATMs');
    }

    /**
     * As páginas de erro são servidas pelo handler de excepções, fora do fluxo
     * normal — vale a pena confirmar que ainda apanham o idioma da sessão.
     */
    public function test_the_not_found_page_follows_the_chosen_language()
    {
        $this->get('/uma-pagina-que-nao-existe')
            ->assertNotFound()
            ->assertSee('Página Não Encontrada');

        $this->get(route('locale.switch', 'en'));

        $this->get('/uma-pagina-que-nao-existe')
            ->assertNotFound()
            ->assertSee('Page not found')
            ->assertDontSee('Página Não Encontrada');
    }

    public function test_the_verification_email_follows_the_chosen_language()
    {
        $utilizador = User::factory()->company()->create();

        $portugues = view('emails.company-verification', [
            'user' => $utilizador,
            'url' => 'https://exemplo.test/confirmar',
        ])->render();

        $this->assertStringContainsString('Confirme o email da sua empresa', $portugues);

        $this->app->setLocale('en');

        $ingles = view('emails.company-verification', [
            'user' => $utilizador,
            'url' => 'https://exemplo.test/confirmar',
        ])->render();

        $this->assertStringContainsString('Confirm your company email', $ingles);
        $this->assertStringNotContainsString('Confirme o email', $ingles);
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

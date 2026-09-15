<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Country;
use App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * O portal passou a publicar vagas de outros países.
 *
 * O que estes testes guardam é sobretudo a regra do meio: quem não diz de que
 * país é a vaga está a publicar para Angola, que é o que todas as vagas do
 * portal eram até aqui. Essa regra vale na API, no painel das empresas e em
 * qualquer outro sítio que crie vagas.
 */
class JobCountryTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_country_list_ships_with_the_migration()
    {
        // As migrações trazem a lista consigo — em produção publica-se com um
        // `git pull` e um `artisan migrate`, sem passar por um seeder.
        $this->assertSame(23, Country::count());

        $this->assertSame(['Angola', 'AO'], [Country::find(1)->name, Country::find(1)->code]);
        $this->assertSame(['Brasil', 'BR'], [Country::find(2)->name, Country::find(2)->code]);

        // Uma amostra dos países de língua espanhola.
        foreach (['ES', 'MX', 'AR', 'CO', 'PE', 'CL', 'GQ', 'PR'] as $codigo) {
            $this->assertTrue(
                Country::where('code', $codigo)->exists(),
                "Falta o país $codigo na lista."
            );
        }
    }

    public function test_every_country_has_a_two_letter_iso_code()
    {
        // É o código que vai para o addressCountry do JobPosting; o Google
        // espera o ISO 3166-1 alfa-2 e nada mais.
        foreach (Country::all() as $pais) {
            $this->assertMatchesRegularExpression('/^[A-Z]{2}$/', $pais->code, "Código inválido: {$pais->code}");
            $this->assertNotSame('', $pais->name_en, "Falta o nome em inglês de {$pais->name}.");
        }

        $this->assertSame(Country::count(), Country::distinct()->count('code'));
    }

    public function test_a_job_created_without_a_country_belongs_to_angola()
    {
        $vaga = Job::create([
            'title' => 'Técnico de Manutenção',
            'company' => 'Empresa Teste',
            'location' => 'Luanda',
            'description' => '<p>Descrição</p>',
            'email_or_link' => 'vagas@exemplo.ao',
        ]);

        $this->assertSame('AO', $vaga->fresh()->country->code);
    }

    public function test_the_api_defaults_to_angola_when_no_country_is_sent()
    {
        // É assim que a importação publica hoje — sem campo de país nenhum.
        $resposta = $this->postJson('/api/job/create', [
            'title' => 'Motorista',
            'company' => 'Transportes Lda',
            'location' => 'Benguela',
            'description' => '<p>Descrição</p>',
            'email_or_link' => 'rh@exemplo.ao',
        ]);

        $resposta->assertOk();
        $this->assertSame('AO', Job::latest('id')->first()->country->code);
    }

    /**
     * @dataProvider paisesIndicados
     */
    public function test_the_api_accepts_the_country_by_code_or_by_name(string $campo, string $valor, string $esperado)
    {
        $resposta = $this->postJson('/api/job/create', [
            'title' => 'Analista',
            'company' => 'Empresa Teste',
            'location' => 'Cidade',
            'description' => '<p>Descrição</p>',
            'email_or_link' => 'rh@exemplo.com',
            $campo => $valor,
        ]);

        $resposta->assertOk();
        $this->assertSame($esperado, Job::latest('id')->first()->country->code);
    }

    public static function paisesIndicados(): array
    {
        return [
            'código ISO' => ['country', 'BR', 'BR'],
            'código em minúsculas' => ['country', 'es', 'ES'],
            'nome em português' => ['country', 'México', 'MX'],
            'nome em inglês' => ['country', 'Spain', 'ES'],
            'country_code' => ['country_code', 'AR', 'AR'],
            'country_id' => ['country_id', '2', 'BR'],
        ];
    }

    public function test_the_api_refuses_a_country_it_does_not_know()
    {
        // Enganar-se no código e ver a vaga sair para Angola era pior do que o
        // erro: quem importa não daria pela troca.
        $resposta = $this->postJson('/api/job/create', [
            'title' => 'Analista',
            'company' => 'Empresa Teste',
            'location' => 'Cidade',
            'description' => '<p>Descrição</p>',
            'email_or_link' => 'rh@exemplo.com',
            'country' => 'XX',
        ]);

        $resposta->assertStatus(422);
        $this->assertSame(0, Job::count());
    }

    public function test_the_job_page_says_which_country_the_job_is_for()
    {
        $vaga = Job::factory()->noPais('BR')->create(['title' => 'Vaga no Brasil']);

        $this->get('/vagas/' . $vaga->slug)
            ->assertOk()
            ->assertSee('Brasil');
    }

    /**
     * O addressCountry do JobPosting esteve escrito à mão como "AO" em todas as
     * vagas. Agora sai do país de cada uma.
     */
    public function test_the_structured_data_carries_the_job_country()
    {
        $angolana = Job::factory()->create();
        $espanhola = Job::factory()->noPais('ES')->create();

        $this->assertSame('AO', $this->paisDoJobPosting($angolana->slug));
        $this->assertSame('ES', $this->paisDoJobPosting($espanhola->slug));
    }

    /**
     * Lê o addressCountry do JobPosting da página de uma vaga.
     *
     * Procurar o texto na página inteira não servia: o cabeçalho do site tem os
     * seus próprios dados estruturados, com a morada do Angola Emprego — essa é
     * mesmo em Angola e continua a dizer "AO", esteja a vaga onde estiver.
     */
    private function paisDoJobPosting(string $slug): ?string
    {
        $html = $this->get('/vagas/' . $slug)->assertOk()->getContent();

        preg_match_all(
            '#<script type="application/ld\+json">(.*?)</script>#s',
            $html,
            $blocos
        );

        foreach ($blocos[1] as $bloco) {
            $dados = json_decode(trim($bloco), true);

            if (($dados['@type'] ?? null) === 'JobPosting') {
                return $dados['jobLocation']['address']['addressCountry'] ?? null;
            }
        }

        $this->fail('A página da vaga não traz dados estruturados de JobPosting.');
    }

    /**
     * O bloco do JobPosting era montado com {{ }}, que escapa para HTML e não
     * para JSON. Bastava uma aspa ou uma quebra de linha na descrição — coisa
     * comum num anúncio — para deixar de ser JSON válido e o Google o ignorar.
     */
    public function test_the_structured_data_survives_a_description_full_of_quotes()
    {
        $vaga = Job::factory()->create([
            'title' => 'Vaga "especial" — atenção',
            'company' => 'Empresa "A & B" Lda',
            'description' => "<p>Procuramos alguém com \"experiência\".</p>\n<p>Requisitos:\n- Um\\dois</p>",
        ]);

        $this->assertSame('AO', $this->paisDoJobPosting($vaga->slug));
    }

    public function test_a_company_publishes_to_angola_unless_it_picks_another_country()
    {
        $empresa = Company::factory()->create(['name' => 'Minha Empresa', 'slug' => 'minha-empresa']);

        $this->actingAs($empresa->user)
            ->post(route('company.jobs.store'), [
                'title' => 'Vaga sem país',
                'location' => 'Luanda',
                'description' => 'Descrição da vaga.',
            ])
            ->assertRedirect(route('company.dashboard'));

        $this->assertSame('AO', Job::where('title', 'Vaga sem país')->first()->country->code);

        $brasil = Country::where('code', 'BR')->firstOrFail();

        $this->actingAs($empresa->user)
            ->post(route('company.jobs.store'), [
                'title' => 'Vaga no Brasil',
                'location' => 'São Paulo',
                'country_id' => $brasil->id,
                'description' => 'Descrição da vaga.',
            ])
            ->assertRedirect(route('company.dashboard'));

        $this->assertSame('BR', Job::where('title', 'Vaga no Brasil')->first()->country->code);
    }

    public function test_the_job_form_offers_the_countries()
    {
        $empresa = Company::factory()->create();

        $this->actingAs($empresa->user)
            ->get(route('company.jobs.create'))
            ->assertOk()
            ->assertSee('Angola')
            ->assertSee('Brasil')
            ->assertSee('Espanha');
    }

    public function test_the_country_name_follows_the_language_of_the_page()
    {
        $vaga = Job::factory()->noPais('ES')->create();

        $this->get('/vagas/' . $vaga->slug)->assertOk()->assertSee('Espanha');

        $this->get(route('locale.switch', 'en'));

        $this->get('/vagas/' . $vaga->slug)
            ->assertOk()
            ->assertSee('Spain')
            ->assertDontSee('Espanha');
    }

    /**
     * A capa de uma vaga é gerada no momento em que ela é criada. O que este
     * teste guarda é a ligação: uma vaga do Brasil tem de sair com a capa verde
     * e amarela, e uma de Angola com a azul de sempre.
     */
    public function test_a_brazilian_job_gets_the_green_and_yellow_cover()
    {
        // Sem Storage::fake: o gerador escreve com storage_path() em vez de
        // passar pelo disco do Laravel, por isso um disco falso não o apanharia.
        // As capas ficam mesmo em storage/app/public e apagam-se no fim.
        $angolana = Job::factory()->create(['image' => null]);
        $brasileira = Job::factory()->noPais('BR')->create(['image' => null, 'location' => 'São Paulo']);

        try {
            $verdeDoBrasil = $this->verdeMenosAzul($brasileira->fresh()->image);
            $verdeDeAngola = $this->verdeMenosAzul($angolana->fresh()->image);

            $this->assertGreaterThan(
                0,
                $verdeDoBrasil,
                'A capa da vaga do Brasil devia ter mais verde do que azul.'
            );

            $this->assertLessThan(
                0,
                $verdeDeAngola,
                'A capa da vaga de Angola devia continuar a ter mais azul do que verde.'
            );
        } finally {
            foreach ([$angolana, $brasileira] as $vaga) {
                $this->apagarCapa($vaga->fresh()->image);
            }
        }
    }

    /** Apaga uma capa gerada durante o teste, e a miniatura que saiu dela. */
    private function apagarCapa(?string $caminho): void
    {
        if ($caminho === null) {
            return;
        }

        @unlink(storage_path('app/public/' . $caminho));
        @unlink(storage_path('app/public/thumb/' . $caminho));
    }

    /**
     * Quanto é que o verde pesa mais do que o azul numa capa. Positivo é uma
     * capa esverdeada, negativo é uma capa azulada.
     */
    private function verdeMenosAzul(?string $caminho): float
    {
        $this->assertNotNull($caminho, 'A vaga ficou sem capa.');

        $ficheiro = storage_path('app/public/' . $caminho);
        $this->assertFileExists($ficheiro);

        $imagem = imagecreatefrompng($ficheiro);
        $verde = 0;
        $azul = 0;
        $n = 0;

        for ($y = 0; $y < imagesy($imagem); $y += 5) {
            for ($x = 0; $x < imagesx($imagem); $x += 5) {
                $cor = imagecolorat($imagem, $x, $y);
                $verde += ($cor >> 8) & 255;
                $azul += $cor & 255;
                $n++;
            }
        }

        imagedestroy($imagem);

        return ($verde - $azul) / $n;
    }

    public function test_the_flag_comes_from_the_country_code()
    {
        $this->assertSame('🇦🇴', Country::where('code', 'AO')->first()->bandeira);
        $this->assertSame('🇧🇷', Country::where('code', 'BR')->first()->bandeira);
        $this->assertSame('', (new Country(['code' => '']))->bandeira);
    }

    public function test_the_selector_puts_angola_first()
    {
        $lista = Country::paraSelector();

        $this->assertSame('AO', $lista->first()->code);
        $this->assertCount(23, $lista);
    }
}

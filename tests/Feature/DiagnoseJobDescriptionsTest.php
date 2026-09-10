<?php

namespace Tests\Feature;

use App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiagnoseJobDescriptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_reports_how_many_adverts_can_be_read()
    {
        Job::factory()->create([
            'description' => "Vaga de Contabilista\n\nRequisitos:\nCédula profissional\nExperiência com Primavera",
        ]);
        Job::factory()->create([
            'description' => 'Precisamos de um ajudante de cozinha. Contactos: 923000000',
        ]);

        $this->artisan('cv:diagnosticar')
            ->expectsOutputToContain('Vagas analisadas: 2')
            ->expectsOutputToContain('Com requisitos lidos: 1 (50%)')
            ->assertSuccessful();
    }

    public function test_it_surfaces_headings_it_does_not_yet_know()
    {
        Job::factory()->create([
            'description' => "Vaga de Servente\n\nPRECISA-SE DE:\nForça física\nDisponibilidade imediata",
        ]);

        $this->artisan('cv:diagnosticar')
            ->expectsOutputToContain('precisa-se de')
            ->assertSuccessful();
    }

    public function test_it_exports_the_adverts_that_failed()
    {
        Job::factory()->create(['description' => 'Anúncio sem secção nenhuma de requisitos.']);
        $ficheiro = storage_path('app/diagnostico-teste.json');

        $this->artisan('cv:diagnosticar', ['--exportar' => $ficheiro])->assertSuccessful();

        $this->assertFileExists($ficheiro);
        $this->assertStringContainsString('sem secção nenhuma', file_get_contents($ficheiro));

        @unlink($ficheiro);
    }

    public function test_it_says_so_when_there_are_no_adverts()
    {
        $this->artisan('cv:diagnosticar')->assertFailed();
    }
}

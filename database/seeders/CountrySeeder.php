<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

/**
 * Sincroniza a tabela dos países com a lista em database/data/paises.php.
 *
 * A migração já insere a lista de raiz; este seeder serve para depois: juntar um
 * país novo à lista e correr `php artisan db:seed --class=CountrySeeder` em vez
 * de escrever mais uma migração. Só acrescenta ou corrige nomes — nunca apaga um
 * país, que teria vagas agarradas a ele.
 */
class CountrySeeder extends Seeder
{
    public function run()
    {
        foreach (require database_path('data/paises.php') as $pais) {
            Country::updateOrCreate(
                ['code' => $pais['code']],
                ['name' => $pais['name'], 'name_en' => $pais['name_en']]
            );
        }
    }
}

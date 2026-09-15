<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabela dos países onde se publicam vagas.
 *
 * A lista entra aqui, e não só num seeder, porque a publicação em produção é um
 * `git pull` seguido — quando é preciso — de `php artisan migrate`. Se os países
 * dependessem de um `db:seed` à parte, a coluna country_id das vagas ficaria a
 * apontar para uma tabela vazia até alguém se lembrar de o correr.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');                  // Nome em português
            $table->string('name_en');               // Nome em inglês, para o idioma do site
            $table->string('code', 2)->unique();     // ISO 3166-1 alfa-2: AO, BR, ES...
            $table->timestamps();
        });

        $agora = now();

        DB::table('countries')->insert(array_map(
            fn (array $pais) => $pais + ['created_at' => $agora, 'updated_at' => $agora],
            require database_path('data/paises.php')
        ));
    }

    public function down()
    {
        Schema::dropIfExists('countries');
    }
};

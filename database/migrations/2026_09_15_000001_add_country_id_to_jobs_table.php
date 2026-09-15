<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Liga cada vaga a um país.
 *
 * Todas as vagas que já estão na base de dados foram publicadas para Angola, por
 * isso a coluna nasce com Angola por omissão e as linhas antigas ficam com ela.
 * O mesmo valor por omissão protege quem escreva directamente na base de dados;
 * o Job e a API tratam do caso de a vaga chegar sem país.
 */
return new class extends Migration
{
    public function up()
    {
        $angola = (int) (DB::table('countries')->where('code', 'AO')->value('id') ?? 1);

        Schema::table('jobs', function (Blueprint $table) use ($angola) {
            $table->unsignedBigInteger('country_id')->default($angola)->after('location');

            $table->foreign('country_id')->references('id')->on('countries')->restrictOnDelete();
        });

        // O valor por omissão já preenche as linhas existentes em MySQL e SQLite,
        // mas deixar isto explícito custa uma query e não deixa margem para dúvida.
        DB::table('jobs')->whereNull('country_id')->update(['country_id' => $angola]);
    }

    public function down()
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
        });
    }
};

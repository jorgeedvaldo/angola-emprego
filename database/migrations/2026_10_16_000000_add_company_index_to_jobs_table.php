<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Índice na coluna `company` das vagas.
 *
 * A barra lateral de /vagas agrupa as vagas por empresa para mostrar as 15 que
 * mais contratam (GROUP BY company). Essa consulta corre em TODAS as visitas
 * a /vagas, com ou sem filtros, e sem índice é uma varrimento completo da
 * tabela seguido de ordenação em ficheiro — a consulta mais cara de uma das
 * páginas mais visitadas do site.
 */
return new class extends Migration
{
    private const INDICE = 'jobs_company_index';

    public function up(): void
    {
        if (!Schema::hasTable('jobs') || !Schema::hasColumn('jobs', 'company') || $this->jaExiste()) {
            return;
        }

        Schema::table('jobs', function ($tabela) {
            $tabela->index('company');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('jobs') && $this->jaExiste()) {
            Schema::table('jobs', fn ($tabela) => $tabela->dropIndex(self::INDICE));
        }
    }

    /**
     * O Laravel 9 não sabe perguntar se um índice existe, e correr a migração
     * duas vezes num servidor onde ela já passou não pode rebentar.
     */
    private function jaExiste(): bool
    {
        $ligacao = Schema::getConnection();

        try {
            if ($ligacao->getDriverName() === 'sqlite') {
                return DB::table('sqlite_master')
                    ->where('type', 'index')
                    ->where('name', self::INDICE)
                    ->exists();
            }

            return collect($ligacao->select('SHOW INDEX FROM `jobs`'))
                ->contains(fn ($linha) => ($linha->Key_name ?? null) === self::INDICE);
        } catch (\Throwable $e) {
            return false;
        }
    }
};

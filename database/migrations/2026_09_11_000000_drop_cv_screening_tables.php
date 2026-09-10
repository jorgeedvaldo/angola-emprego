<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * O analisador de CVs para recrutadores passou a ser uma ferramenta pública que
 * não guarda nada: os CVs são enviados apenas durante a análise e nunca chegam a
 * ser gravados. Esta migração remove as tabelas da primeira versão, para o caso
 * de já terem sido criadas antes da mudança.
 *
 * A análise de candidaturas das empresas (jobs.description_vector e
 * job_applications.cv_vector) não é afectada — essa continua a gravar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('cv_screening_candidates');
        Schema::dropIfExists('cv_screenings');
    }

    public function down(): void
    {
        // Sem retorno: as tabelas deixaram de fazer parte do modelo de dados.
    }
};

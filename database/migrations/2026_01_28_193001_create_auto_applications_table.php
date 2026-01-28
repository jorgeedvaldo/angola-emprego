<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('auto_applications', function (Blueprint $table) {
            $table->id();
            
            // Chaves Estrangeiras
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tracked_job_id')->constrained('tracked_jobs')->onDelete('cascade');

            // O Status vive aqui (cada user tem o seu status para a mesma vaga)
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            
            $table->timestamps();

            // Impede que o mesmo user seja associado à mesma vaga duas vezes
            $table->unique(['user_id', 'tracked_job_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_applications');
    }
};

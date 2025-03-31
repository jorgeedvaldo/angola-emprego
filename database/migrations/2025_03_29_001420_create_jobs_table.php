<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('title');                           // Título do post
            $table->string('slug')->unique()->nullable();     // Slug único para URLs amigáveis
            $table->string('company');                       // Nome da empresa
            $table->string('location');                     // Localização
            $table->text('description');                   // Descrição ou conteúdo do post
            $table->string('email_or_link');              // Email ou link relacionado
            $table->string('image')->nullable();         // Caminho da imagem (opcional)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jobs');
    }
};

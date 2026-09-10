<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cv_screenings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('title');
            $table->longText('description');
            $table->json('description_vector')->nullable();
            $table->string('description_vector_model')->nullable();
            $table->timestamp('description_vector_generated_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });

        Schema::create('cv_screening_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_screening_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name');
            $table->longText('cv_text')->nullable();
            $table->json('cv_vector')->nullable();
            $table->string('cv_vector_model')->nullable();
            $table->timestamp('cv_analyzed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cv_screening_candidates');
        Schema::dropIfExists('cv_screenings');
    }
};

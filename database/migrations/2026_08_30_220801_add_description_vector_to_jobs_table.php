<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->json('description_vector')->nullable()->after('description');
            $table->timestamp('description_vector_generated_at')->nullable()->after('description_vector');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn(['description_vector', 'description_vector_generated_at']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->string('description_vector_model')->nullable()->after('description_vector');
        });

        Schema::table('job_applications', function (Blueprint $table) {
            $table->string('cv_vector_model')->nullable()->after('cv_vector');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn('description_vector_model');
        });

        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn('cv_vector_model');
        });
    }
};

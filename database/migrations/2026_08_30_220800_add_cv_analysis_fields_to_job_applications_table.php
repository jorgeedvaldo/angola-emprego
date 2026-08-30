<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->longText('cv_text')->nullable()->after('message');
            $table->json('cv_vector')->nullable()->after('cv_text');
            $table->timestamp('cv_analyzed_at')->nullable()->after('cv_vector');
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['cv_text', 'cv_vector', 'cv_analyzed_at']);
        });
    }
};

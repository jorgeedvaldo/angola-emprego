<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->string('attachment_path');
            $table->string('attachment_name')->nullable();
            $table->string('status')->default('new');
            $table->timestamps();

            $table->unique(['job_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};

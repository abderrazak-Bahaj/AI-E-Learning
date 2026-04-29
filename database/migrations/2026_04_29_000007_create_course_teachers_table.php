<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_teachers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('course_id')->index();
            $table->foreignUuid('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['LEAD', 'ASSISTANT', 'GUEST'])->default('ASSISTANT');
            $table->timestamps();

            $table->foreign('course_id')
                ->references('id')
                ->on('courses')
                ->cascadeOnDelete();

            $table->unique(['course_id', 'teacher_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_teachers');
    }
};

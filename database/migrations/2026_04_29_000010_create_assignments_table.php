<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['QUIZ', 'ESSAY', 'MULTIPLE_CHOICE', 'TRUE_FALSE', 'MATCHING']);
            $table->integer('time_limit')->nullable()->comment('Time limit in minutes from when student starts');
            $table->integer('max_attempts')->default(1)->comment('Maximum allowed attempts');
            $table->integer('total_points')->default(100);
            $table->integer('passing_score')->default(60)->comment('Minimum score to pass');
            $table->enum('status', ['DRAFT', 'PUBLISHED', 'ARCHIVED'])->default('DRAFT');
            $table->uuid('course_id')->index();
            $table->uuid('lesson_id')->nullable()->index();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('course_id')
                ->references('id')
                ->on('courses')
                ->cascadeOnDelete();

            $table->foreign('lesson_id')
                ->references('id')
                ->on('lessons')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};

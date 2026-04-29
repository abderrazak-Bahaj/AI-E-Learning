<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description');
            $table->string('image_url')->nullable();
            $table->decimal('price', 8, 2)->default(0);
            $table->enum('status', ['DRAFT', 'PUBLISHED', 'ARCHIVED'])->default('DRAFT');
            $table->enum('level', ['BEGINNER', 'INTERMEDIATE', 'ADVANCED'])->default('BEGINNER');
            $table->json('skills')->nullable();
            $table->string('language')->default('English');
            $table->integer('duration')->default(0)->comment('Total duration in minutes');
            $table->uuid('category_id')->index();
            $table->foreignUuid('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};

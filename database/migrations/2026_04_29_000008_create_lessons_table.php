<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('content');
            $table->string('video_url')->nullable();
            $table->integer('order')->default(0);
            $table->integer('section')->default(1);
            $table->integer('duration')->default(0)->comment('Duration in minutes');
            $table->boolean('is_free_preview')->default(false);
            $table->enum('status', ['DRAFT', 'PUBLISHED'])->default('DRAFT');
            $table->uuid('course_id')->index();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('course_id')
                ->references('id')
                ->on('courses')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};

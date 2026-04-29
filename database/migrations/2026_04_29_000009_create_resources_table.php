<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('file_url');
            $table->integer('order')->default(1);
            $table->enum('type', ['PDF', 'VIDEO', 'AUDIO', 'LINK', 'OTHER']);
            $table->boolean('is_preview')->default(false);
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
        Schema::dropIfExists('resources');
    }
};

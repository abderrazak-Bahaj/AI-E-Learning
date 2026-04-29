<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_questions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->text('question_text');
            $table->enum('question_type', ['MULTIPLE_CHOICE', 'TRUE_FALSE', 'SHORT_ANSWER', 'ESSAY']);
            $table->integer('points')->default(1);
            $table->integer('order')->default(0);
            $table->text('explanation')->nullable()->comment('Explanation shown after answering');
            $table->uuid('assignment_id')->index();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('assignment_id')
                ->references('id')
                ->on('assignments')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_questions');
    }
};

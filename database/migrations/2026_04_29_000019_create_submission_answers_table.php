<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_answers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('submission_id')->index();
            $table->uuid('question_id')->index();
            $table->uuid('selected_option_id')->nullable()->index()->comment('For multiple choice / true-false');
            $table->text('answer')->nullable()->comment('For short answer / essay');
            $table->boolean('is_correct')->nullable();
            $table->float('score')->nullable();
            $table->text('feedback')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('submission_id')
                ->references('id')
                ->on('submissions')
                ->cascadeOnDelete();

            $table->foreign('question_id')
                ->references('id')
                ->on('assignment_questions')
                ->cascadeOnDelete();

            $table->foreign('selected_option_id')
                ->references('id')
                ->on('assignment_options')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_answers');
    }
};

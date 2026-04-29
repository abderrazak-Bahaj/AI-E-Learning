<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_options', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->integer('order')->default(0);
            $table->uuid('question_id')->index();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('question_id')
                ->references('id')
                ->on('assignment_questions')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_options');
    }
};

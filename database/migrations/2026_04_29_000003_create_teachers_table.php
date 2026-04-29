<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('specialization')->nullable();
            $table->string('qualification')->nullable();
            $table->text('expertise')->nullable();
            $table->json('education')->nullable();
            $table->json('certifications')->nullable();
            $table->decimal('rating', 3, 2)->nullable();
            $table->integer('years_of_experience')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};

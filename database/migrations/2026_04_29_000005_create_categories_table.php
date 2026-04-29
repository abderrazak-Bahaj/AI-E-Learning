<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->uuid('parent_id')->nullable()->index();
            $table->string('icon')->nullable();
            $table->integer('order')->default(0);
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
            $table->softDeletes();
            $table->timestamps();
        });

        // Self-referencing FK must be added after the table (and its PK) are fully created.
        // PostgreSQL requires the referenced column to have a committed unique constraint first.
        Schema::table('categories', function (Blueprint $table): void {
            $table->foreign('parent_id')
                ->references('id')
                ->on('categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->dropForeign(['parent_id']);
        });

        Schema::dropIfExists('categories');
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('users')->cascadeOnDelete();
            $table->uuid('assignment_id')->index();
            $table->integer('attempt_number')->default(1);
            $table->float('score')->nullable();
            $table->boolean('is_passed')->default(false);
            $table->text('feedback')->nullable();
            $table->enum('status', ['IN_PROGRESS', 'SUBMITTED', 'GRADED'])->default('IN_PROGRESS');
            $table->timestamp('submitted_at')->nullable();
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
        Schema::dropIfExists('submissions');
    }
};

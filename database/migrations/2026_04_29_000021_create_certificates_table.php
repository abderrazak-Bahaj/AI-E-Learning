<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('certificate_number')->unique();
            $table->foreignUuid('student_id')->constrained('users')->cascadeOnDelete();
            $table->uuid('course_id')->index();
            $table->uuid('enrollment_id')->nullable()->index();
            $table->enum('status', ['PENDING', 'GENERATING', 'GENERATED', 'FAILED'])->default('PENDING');
            $table->string('certificate_url')->nullable();
            $table->string('template_url')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('issue_date')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('course_id')
                ->references('id')
                ->on('courses')
                ->cascadeOnDelete();

            $table->foreign('enrollment_id')
                ->references('id')
                ->on('enrollments')
                ->nullOnDelete();

            // One certificate per student per course
            $table->unique(['student_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};

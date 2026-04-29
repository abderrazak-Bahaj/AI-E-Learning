<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('student_id')->nullable()->unique();
            $table->enum('enrollment_status', ['ACTIVE', 'INACTIVE', 'GRADUATED', 'SUSPENDED'])->default('ACTIVE');
            $table->string('education_level')->nullable();
            $table->string('major')->nullable();
            $table->json('interests')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->json('learning_preferences')->nullable();
            $table->decimal('gpa', 3, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

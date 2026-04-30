<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Composite indexes for the most common query patterns.
 * These complement the single-column indexes already created by the FK constraints.
 */
return new class extends Migration
{
    public function up(): void
    {
        // enrollments — student's course list + status filter
        Schema::table('enrollments', function (Blueprint $table): void {
            $table->index(['student_id', 'status'], 'enrollments_student_status_idx');
            $table->index(['course_id', 'status'], 'enrollments_course_status_idx');
        });

        // lesson_progress — student progress per course
        Schema::table('lesson_progress', function (Blueprint $table): void {
            $table->index(['student_id', 'course_id', 'status'], 'lesson_progress_student_course_status_idx');
        });

        // submissions — student submissions per assignment + grading queue
        Schema::table('submissions', function (Blueprint $table): void {
            $table->index(['student_id', 'assignment_id'], 'submissions_student_assignment_idx');
            $table->index(['assignment_id', 'status'], 'submissions_assignment_status_idx');
        });

        // payments — user payment history + status filter
        Schema::table('payments', function (Blueprint $table): void {
            $table->index(['user_id', 'status'], 'payments_user_status_idx');
        });

        // invoices — user invoice list + status filter
        Schema::table('invoices', function (Blueprint $table): void {
            $table->index(['user_id', 'status'], 'invoices_user_status_idx');
        });

        // courses — teacher course list + status filter
        Schema::table('courses', function (Blueprint $table): void {
            $table->index(['teacher_id', 'status'], 'courses_teacher_status_idx');
            $table->index(['category_id', 'status'], 'courses_category_status_idx');
        });

        // certificates — student certificates by status
        Schema::table('certificates', function (Blueprint $table): void {
            $table->index(['student_id', 'status'], 'certificates_student_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table): void {
            $table->dropIndex('enrollments_student_status_idx');
            $table->dropIndex('enrollments_course_status_idx');
        });

        Schema::table('lesson_progress', function (Blueprint $table): void {
            $table->dropIndex('lesson_progress_student_course_status_idx');
        });

        Schema::table('submissions', function (Blueprint $table): void {
            $table->dropIndex('submissions_student_assignment_idx');
            $table->dropIndex('submissions_assignment_status_idx');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->dropIndex('payments_user_status_idx');
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropIndex('invoices_user_status_idx');
        });

        Schema::table('courses', function (Blueprint $table): void {
            $table->dropIndex('courses_teacher_status_idx');
            $table->dropIndex('courses_category_status_idx');
        });

        Schema::table('certificates', function (Blueprint $table): void {
            $table->dropIndex('certificates_student_status_idx');
        });
    }
};

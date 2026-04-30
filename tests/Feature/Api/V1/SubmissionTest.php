<?php

declare(strict_types=1);

use App\Models\Assignment;
use App\Models\AssignmentOption;
use App\Models\AssignmentQuestion;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Passport\Passport;

uses(LazilyRefreshDatabase::class);

describe('Submission', function (): void {
    it('student can submit answers to a multiple choice assignment', function (): void {
        $student = User::factory()->student()->create();
        $course = Course::factory()->create();
        Enrollment::factory()->create(['student_id' => $student->id, 'course_id' => $course->id]);

        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'type' => 'MULTIPLE_CHOICE',
            'status' => 'PUBLISHED',
            'max_attempts' => 3,
            'total_points' => 100,
            'passing_score' => 60,
        ]);

        $question = AssignmentQuestion::factory()->create([
            'assignment_id' => $assignment->id,
            'question_type' => 'MULTIPLE_CHOICE',
            'points' => 10,
        ]);

        $correct = AssignmentOption::factory()->correct()->create(['question_id' => $question->id]);
        AssignmentOption::factory()->create(['question_id' => $question->id]);

        Passport::actingAs($student);

        $this->postJson("/api/v1/courses/{$course->id}/assignments/{$assignment->id}/submissions", [
            'answers' => [
                ['question_id' => $question->id, 'selected_option_id' => $correct->id],
            ],
        ])->assertCreated()
            ->assertJsonPath('data.status', 'GRADED')
            ->assertJsonPath('data.is_passed', true);
    });

    it('auto-grades multiple choice correctly', function (): void {
        $student = User::factory()->student()->create();
        $course = Course::factory()->create();
        Enrollment::factory()->create(['student_id' => $student->id, 'course_id' => $course->id]);

        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'type' => 'MULTIPLE_CHOICE',
            'status' => 'PUBLISHED',
            'max_attempts' => 3,
            'total_points' => 100,
            'passing_score' => 60,
        ]);

        $question = AssignmentQuestion::factory()->create([
            'assignment_id' => $assignment->id,
            'question_type' => 'MULTIPLE_CHOICE',
            'points' => 10,
        ]);

        $wrong = AssignmentOption::factory()->create(['question_id' => $question->id, 'is_correct' => false]);
        AssignmentOption::factory()->correct()->create(['question_id' => $question->id]);

        Passport::actingAs($student);

        $response = $this->postJson("/api/v1/courses/{$course->id}/assignments/{$assignment->id}/submissions", [
            'answers' => [
                ['question_id' => $question->id, 'selected_option_id' => $wrong->id],
            ],
        ])->assertCreated();

        expect($response->json('data.score'))->toEqual(0.0);
        expect($response->json('data.is_passed'))->toBeFalse();
    });

    it('enforces max attempts', function (): void {
        $student = User::factory()->student()->create();
        $course = Course::factory()->create();
        Enrollment::factory()->create(['student_id' => $student->id, 'course_id' => $course->id]);

        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'type' => 'MULTIPLE_CHOICE',
            'status' => 'PUBLISHED',
            'max_attempts' => 1,
        ]);

        Submission::factory()->create([
            'student_id' => $student->id,
            'assignment_id' => $assignment->id,
        ]);

        Passport::actingAs($student);

        $this->postJson("/api/v1/courses/{$course->id}/assignments/{$assignment->id}/submissions", [
            'answers' => [],
        ])->assertStatus(422);
    });

    it('teacher can grade a submission', function (): void {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $student = User::factory()->student()->create();

        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'type' => 'ESSAY',
            'status' => 'PUBLISHED',
            'total_points' => 100,
            'passing_score' => 60,
        ]);

        $submission = Submission::factory()->create([
            'student_id' => $student->id,
            'assignment_id' => $assignment->id,
            'status' => 'SUBMITTED',
        ]);

        Passport::actingAs($teacher);

        $this->patchJson("/api/v1/courses/{$course->id}/assignments/{$assignment->id}/submissions/{$submission->id}/grade", [
            'score' => 85,
            'feedback' => 'Great work!',
        ])->assertSuccessful()
            ->assertJsonPath('data.status', 'GRADED')
            ->assertJsonPath('data.is_passed', true);

        expect($submission->fresh()->score)->toBe(85.0);
    });

    it('student cannot grade a submission', function (): void {
        $student = User::factory()->student()->create();
        $course = Course::factory()->create();
        $assignment = Assignment::factory()->create(['course_id' => $course->id]);
        $submission = Submission::factory()->create([
            'student_id' => $student->id,
            'assignment_id' => $assignment->id,
        ]);
        Passport::actingAs($student);

        $this->patchJson("/api/v1/courses/{$course->id}/assignments/{$assignment->id}/submissions/{$submission->id}/grade", [
            'score' => 100,
        ])->assertForbidden();
    });
});

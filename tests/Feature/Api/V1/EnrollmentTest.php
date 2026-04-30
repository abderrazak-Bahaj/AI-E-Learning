<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Notifications\EnrollmentConfirmed;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Passport\Passport;

uses(LazilyRefreshDatabase::class);

describe('Enrollment', function (): void {
    it('student can enroll in a free course', function (): void {
        Notification::fake();

        $student = User::factory()->student()->create();
        $course = Course::factory()->free()->published()->create();
        Passport::actingAs($student);

        $this->postJson('/api/v1/enrollments', ['course_id' => $course->id])
            ->assertCreated()
            ->assertJsonPath('data.status', 'ACTIVE');

        $this->assertDatabaseHas('enrollments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'ACTIVE',
        ]);

        Notification::assertSentTo($student, EnrollmentConfirmed::class);
    });

    it('prevents duplicate enrollment', function (): void {
        $student = User::factory()->student()->create();
        $course = Course::factory()->free()->published()->create();
        Enrollment::factory()->create(['student_id' => $student->id, 'course_id' => $course->id]);
        Passport::actingAs($student);

        $this->postJson('/api/v1/enrollments', ['course_id' => $course->id])
            ->assertStatus(409);
    });

    it('student can drop an enrollment', function (): void {
        $student = User::factory()->student()->create();
        $course = Course::factory()->published()->create();
        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'ACTIVE',
        ]);
        Passport::actingAs($student);

        $this->deleteJson("/api/v1/enrollments/{$enrollment->id}")
            ->assertSuccessful();

        expect($enrollment->fresh()->status)->toBe('DROPPED');
    });

    it('student cannot drop another student enrollment', function (): void {
        $student = User::factory()->student()->create();
        $other = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->create(['student_id' => $other->id]);
        Passport::actingAs($student);

        $this->deleteJson("/api/v1/enrollments/{$enrollment->id}")
            ->assertForbidden();
    });

    it('teacher cannot enroll', function (): void {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->published()->create();
        Passport::actingAs($teacher);

        $this->postJson('/api/v1/enrollments', ['course_id' => $course->id])
            ->assertForbidden();
    });

    it('lists my enrollments', function (): void {
        $student = User::factory()->student()->create();
        Enrollment::factory(3)->create(['student_id' => $student->id]);
        Enrollment::factory(2)->create(); // other students
        Passport::actingAs($student);

        $this->getJson('/api/v1/enrollments')
            ->assertSuccessful()
            ->assertJsonCount(3, 'data');
    });
});

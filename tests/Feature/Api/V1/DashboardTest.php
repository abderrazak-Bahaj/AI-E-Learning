<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Passport\Passport;

uses(LazilyRefreshDatabase::class);

describe('Dashboard', function (): void {
    it('admin can access admin stats', function (): void {
        $admin = User::factory()->admin()->create();
        Passport::actingAs($admin);

        $this->getJson('/api/v1/dashboard/admin')
            ->assertSuccessful()
            ->assertJsonStructure(['data' => ['users', 'courses', 'enrollments', 'revenue', 'certificates_issued']]);
    });

    it('teacher cannot access admin stats', function (): void {
        $teacher = User::factory()->teacher()->create();
        Passport::actingAs($teacher);

        $this->getJson('/api/v1/dashboard/admin')->assertForbidden();
    });

    it('student cannot access admin stats', function (): void {
        $student = User::factory()->student()->create();
        Passport::actingAs($student);

        $this->getJson('/api/v1/dashboard/admin')->assertForbidden();
    });

    it('teacher can access teacher stats', function (): void {
        $teacher = User::factory()->teacher()->create();
        Passport::actingAs($teacher);

        $this->getJson('/api/v1/dashboard/teacher')
            ->assertSuccessful()
            ->assertJsonStructure(['data' => ['courses', 'students', 'completion_rate', 'pending_submissions', 'revenue']]);
    });

    it('student cannot access teacher stats', function (): void {
        $student = User::factory()->student()->create();
        Passport::actingAs($student);

        $this->getJson('/api/v1/dashboard/teacher')->assertForbidden();
    });

    it('student can access student stats', function (): void {
        $student = User::factory()->student()->create();
        Passport::actingAs($student);

        $this->getJson('/api/v1/dashboard/student')
            ->assertSuccessful()
            ->assertJsonStructure(['data' => ['enrollments', 'certificates_earned', 'submissions']]);
    });

    it('teacher cannot access student stats', function (): void {
        $teacher = User::factory()->teacher()->create();
        Passport::actingAs($teacher);

        $this->getJson('/api/v1/dashboard/student')->assertForbidden();
    });

    it('unauthenticated user cannot access any dashboard', function (): void {
        $this->getJson('/api/v1/dashboard/admin')->assertUnauthorized();
        $this->getJson('/api/v1/dashboard/teacher')->assertUnauthorized();
        $this->getJson('/api/v1/dashboard/student')->assertUnauthorized();
    });
});

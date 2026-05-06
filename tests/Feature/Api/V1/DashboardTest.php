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
            ->assertJsonStructure(['data' => ['overview', 'monthly_stats', 'top_courses', 'enrollment_trends', 'recent_enrollments', 'teacher_performance']]);
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

    // Bug Condition Exploration Test - Property 1
    // **Validates: Requirements 1.1, 1.2, 1.3, 1.4**
    // CRITICAL: This test MUST FAIL on unfixed code - failure confirms the bug exists
    // Expected to fail because backend returns {users, courses, enrollments, revenue}
    // but frontend expects {overview, monthly_stats, top_courses, enrollment_trends, recent_enrollments, teacher_performance}
    it('admin dashboard returns response structure matching frontend DashboardStatistics interface', function (): void {
        $admin = User::factory()->admin()->create();
        Passport::actingAs($admin);

        $response = $this->getJson('/api/v1/dashboard/admin')
            ->assertSuccessful();

        // Assert response contains required top-level keys matching frontend interface
        $response->assertJsonStructure([
            'data' => [
                'overview' => [
                    'total_courses',
                    'total_students',
                    'total_teachers',
                    'total_enrollments',
                    'total_revenue',
                ],
                'monthly_stats' => [
                    '*' => [
                        'month',
                        'total_invoices',
                        'total_revenue',
                    ],
                ],
                'top_courses' => [
                    '*' => [
                        'id',
                        'title',
                        'enrollments_count',
                    ],
                ],
                'enrollment_trends' => [
                    '*' => [
                        'month',
                        'total_enrollments',
                    ],
                ],
                'recent_enrollments' => [
                    '*' => [
                        'id',
                        'user' => [
                            'id',
                            'name',
                            'email',
                        ],
                        'course' => [
                            'id',
                            'title',
                        ],
                        'created_at',
                    ],
                ],
                'teacher_performance' => [
                    '*' => [
                        'id',
                        'name',
                        'courses_count',
                        'total_enrollments',
                        'courses_avg_rating',
                    ],
                ],
            ],
        ]);

        // Verify data types and structure
        $data = $response->json('data');

        expect($data)->toHaveKey('overview')
            ->and($data['overview'])->toBeArray()
            ->and($data['overview'])->toHaveKeys(['total_courses', 'total_students', 'total_teachers', 'total_enrollments', 'total_revenue'])
            ->and($data)->toHaveKey('monthly_stats')
            ->and($data['monthly_stats'])->toBeArray()
            ->and($data)->toHaveKey('enrollment_trends')
            ->and($data['enrollment_trends'])->toBeArray()
            ->and($data)->toHaveKey('recent_enrollments')
            ->and($data['recent_enrollments'])->toBeArray()
            ->and($data)->toHaveKey('teacher_performance')
            ->and($data['teacher_performance'])->toBeArray();
    });

    // Preservation Property Tests - Property 2
    // **Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5**
    // These tests verify that non-admin dashboard functionality remains unchanged
    // Expected to PASS on unfixed code to establish baseline behavior

    it('preserves teacher dashboard response structure after admin dashboard fix', function (): void {
        $teacher = User::factory()->teacher()->create();
        Passport::actingAs($teacher);

        $response = $this->getJson('/api/v1/dashboard/teacher')
            ->assertSuccessful();

        // Verify teacher dashboard structure remains unchanged
        $response->assertJsonStructure([
            'data' => [
                'courses' => [
                    'total',
                    'published',
                    'list' => [
                        '*' => [
                            'id',
                            'title',
                            'status',
                            'enrollments',
                            'lessons',
                        ],
                    ],
                ],
                'students' => [
                    'active',
                    'completed',
                ],
                'completion_rate',
                'pending_submissions',
                'revenue' => [
                    'total',
                    'currency',
                ],
            ],
        ]);

        $data = $response->json('data');

        // Verify structure and data types
        expect($data)->toHaveKey('courses')
            ->and($data['courses'])->toBeArray()
            ->and($data['courses'])->toHaveKeys(['total', 'published', 'list'])
            ->and($data)->toHaveKey('students')
            ->and($data['students'])->toBeArray()
            ->and($data['students'])->toHaveKeys(['active', 'completed'])
            ->and($data)->toHaveKey('completion_rate')
            ->and($data)->toHaveKey('pending_submissions')
            ->and($data)->toHaveKey('revenue')
            ->and($data['revenue'])->toHaveKeys(['total', 'currency']);
    });

    it('preserves student dashboard response structure after admin dashboard fix', function (): void {
        $student = User::factory()->student()->create();
        Passport::actingAs($student);

        $response = $this->getJson('/api/v1/dashboard/student')
            ->assertSuccessful();

        // Verify student dashboard structure remains unchanged
        $response->assertJsonStructure([
            'data' => [
                'enrollments' => [
                    'total',
                    'active',
                    'completed',
                    'dropped',
                ],
                'certificates_earned',
                'submissions' => [
                    'total',
                    'graded',
                    'passed',
                ],
                'recent_enrollments' => [
                    '*' => [
                        'course_id',
                        'course_title',
                        'status',
                        'progress',
                        'enrolled_at',
                    ],
                ],
            ],
        ]);

        $data = $response->json('data');

        // Verify structure and data types
        expect($data)->toHaveKey('enrollments')
            ->and($data['enrollments'])->toBeArray()
            ->and($data['enrollments'])->toHaveKeys(['total', 'active', 'completed', 'dropped'])
            ->and($data)->toHaveKey('certificates_earned')
            ->and($data)->toHaveKey('submissions')
            ->and($data['submissions'])->toBeArray()
            ->and($data['submissions'])->toHaveKeys(['total', 'graded', 'passed'])
            ->and($data)->toHaveKey('recent_enrollments')
            ->and($data['recent_enrollments'])->toBeArray();
    });

    it('preserves authentication error handling for dashboard endpoints', function (): void {
        // Test unauthenticated access returns 401
        $this->getJson('/api/v1/dashboard/admin')->assertUnauthorized();
        $this->getJson('/api/v1/dashboard/teacher')->assertUnauthorized();
        $this->getJson('/api/v1/dashboard/student')->assertUnauthorized();
    });

    it('preserves authorization error handling for dashboard endpoints', function (): void {
        $student = User::factory()->student()->create();
        Passport::actingAs($student);

        // Students cannot access admin or teacher dashboards
        $this->getJson('/api/v1/dashboard/admin')->assertForbidden();
        $this->getJson('/api/v1/dashboard/teacher')->assertForbidden();

        $teacher = User::factory()->teacher()->create();
        Passport::actingAs($teacher);

        // Teachers cannot access admin or student dashboards
        $this->getJson('/api/v1/dashboard/admin')->assertForbidden();
        $this->getJson('/api/v1/dashboard/student')->assertForbidden();
    });
});

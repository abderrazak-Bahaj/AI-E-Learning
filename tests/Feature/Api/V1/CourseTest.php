<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Passport\Passport;

uses(LazilyRefreshDatabase::class);

describe('Course Listing', function (): void {
    it('lists published courses publicly', function (): void {
        Category::factory()->create();
        Course::factory(3)->published()->create();
        Course::factory(2)->draft()->create();

        $this->getJson('/api/v1/courses')
            ->assertSuccessful()
            ->assertJsonCount(3, 'data');
    });

    it('paginates results', function (): void {
        Category::factory()->create();
        Course::factory(20)->published()->create();

        $this->getJson('/api/v1/courses?per_page=5')
            ->assertSuccessful()
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonCount(5, 'data');
    });

    it('caps per_page at 100', function (): void {
        Category::factory()->create();
        Course::factory(5)->published()->create();

        $response = $this->getJson('/api/v1/courses?per_page=999');
        $response->assertSuccessful();
        expect($response->json('meta.per_page'))->toBeLessThanOrEqual(100);
    });

    it('searches by title', function (): void {
        Category::factory()->create();
        Course::factory()->published()->create(['title' => 'Laravel Mastery']);
        Course::factory()->published()->create(['title' => 'Vue.js Basics']);

        $this->getJson('/api/v1/courses?search=Laravel')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Laravel Mastery');
    });

    it('filters by level', function (): void {
        Category::factory()->create();
        Course::factory(2)->published()->create(['level' => 'BEGINNER']);
        Course::factory(3)->published()->create(['level' => 'ADVANCED']);

        $this->getJson('/api/v1/courses?filter[level]=BEGINNER')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data');
    });

    it('sorts by price ascending', function (): void {
        Category::factory()->create();
        Course::factory()->published()->create(['price' => 99.99, 'title' => 'Expensive']);
        Course::factory()->published()->create(['price' => 9.99, 'title' => 'Cheap']);

        $response = $this->getJson('/api/v1/courses?sort=price&order=asc');
        $response->assertSuccessful();
        expect($response->json('data.0.title'))->toBe('Cheap');
    });
});

describe('Course CRUD', function (): void {
    it('shows a single course', function (): void {
        $course = Course::factory()->published()->create();

        $this->getJson("/api/v1/courses/{$course->id}")
            ->assertSuccessful()
            ->assertJsonPath('data.id', $course->id);
    });

    it('teacher can create a course', function (): void {
        $teacher = User::factory()->teacher()->create();
        $category = Category::factory()->create();
        Passport::actingAs($teacher);

        $this->postJson('/api/v1/courses', [
            'title' => 'New Course',
            'description' => 'A great course',
            'price' => 49.99,
            'level' => 'BEGINNER',
            'category_id' => $category->id,
        ])->assertCreated()
            ->assertJsonPath('data.title', 'New Course');
    });

    it('student cannot create a course', function (): void {
        $student = User::factory()->student()->create();
        $category = Category::factory()->create();
        Passport::actingAs($student);

        $this->postJson('/api/v1/courses', [
            'title' => 'Sneaky Course',
            'description' => 'Should fail',
            'price' => 0,
            'level' => 'BEGINNER',
            'category_id' => $category->id,
        ])->assertForbidden();
    });

    it('teacher can update their own course', function (): void {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        Passport::actingAs($teacher);

        $this->putJson("/api/v1/courses/{$course->id}", ['title' => 'Updated Title'])
            ->assertSuccessful()
            ->assertJsonPath('data.title', 'Updated Title');
    });

    it('teacher cannot update another teacher course', function (): void {
        $teacher = User::factory()->teacher()->create();
        $otherTeacher = User::factory()->teacher()->create();
        $other = Course::factory()->create(['teacher_id' => $otherTeacher->id]);
        Passport::actingAs($teacher);

        $this->putJson("/api/v1/courses/{$other->id}", ['title' => 'Hijacked'])
            ->assertForbidden();
    });

    it('teacher can delete their own course (soft delete)', function (): void {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        Passport::actingAs($teacher);

        $this->deleteJson("/api/v1/courses/{$course->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('courses', ['id' => $course->id]);
    });

    it('my-courses returns only teacher courses', function (): void {
        $teacher = User::factory()->teacher()->create();
        $otherTeacher = User::factory()->teacher()->create();
        Course::factory(3)->create(['teacher_id' => $teacher->id]);
        Course::factory(2)->create(['teacher_id' => $otherTeacher->id]);
        Passport::actingAs($teacher);

        $this->getJson('/api/v1/my-courses')
            ->assertSuccessful()
            ->assertJsonCount(3, 'data');
    });
});

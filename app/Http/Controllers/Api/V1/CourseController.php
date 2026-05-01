<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\StoreCourseRequest;
use App\Http\Requests\Api\V1\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CourseController extends ApiController
{
    /**
     * List published courses.
     *
     * Supports pagination, search, filtering, and sorting.
     */
    #[\Dedoc\Scramble\Attributes\QueryParameter('search', description: 'Search in title and description.', type: 'string', example: 'Laravel')]
    #[\Dedoc\Scramble\Attributes\QueryParameter('category', description: 'Filter by category slug.', type: 'string', example: 'web-development')]
    #[\Dedoc\Scramble\Attributes\QueryParameter('filter[level]', description: 'Filter by level.', type: 'string', example: 'BEGINNER')]
    #[\Dedoc\Scramble\Attributes\QueryParameter('filter[language]', description: 'Filter by language.', type: 'string', example: 'English')]
    #[\Dedoc\Scramble\Attributes\QueryParameter('sort', description: 'Sort field: title, price, created_at, duration.', type: 'string', example: 'price')]
    #[\Dedoc\Scramble\Attributes\QueryParameter('order', description: 'Sort direction: asc or desc.', type: 'string', example: 'asc')]
    #[\Dedoc\Scramble\Attributes\QueryParameter('per_page', description: 'Items per page (max 100).', type: 'integer', default: 15, example: 10)]
    #[\Dedoc\Scramble\Attributes\QueryParameter('page', description: 'Page number.', type: 'integer', default: 1, example: 2)]
    public function index(Request $request): JsonResponse
    {
        return $this->paginatedResponse(
            query: Course::query()
                ->published()
                ->with('category', 'teacher')
                ->withCount('lessons', 'enrollments')
                ->when($request->filled('category'), fn ($q) => $q->whereHas(
                    'category', fn ($q) => $q->where('slug', $request->category)
                )),
            request: $request,
            resourceClass: CourseResource::class,
            searchColumns: ['title', 'description'],
            allowedSorts: ['title', 'price', 'created_at', 'duration'],
            allowedFilters: ['status', 'level', 'category_id', 'language'],
        );
    }

    /**
     * Create a new course.
     *
     * Teacher or admin only. The authenticated teacher is automatically set as the course owner.
     */
    public function store(StoreCourseRequest $request): JsonResponse
    {
        $this->authorize('create', Course::class);

        $course = Course::query()->create(array_merge(
            $request->validated(),
            ['teacher_id' => $request->user()->id]
        ));

        return $this->created(
            new CourseResource($course->load('category', 'teacher')),
            'Course created successfully'
        );
    }

    /**
     * Get a single course with lessons and enrollment count.
     */
    public function show(Course $course): JsonResponse
    {
        $course->load('category', 'teacher', 'lessons')->loadCount('enrollments');

        return $this->success(new CourseResource($course));
    }

    /**
     * Update a course.
     *
     * Only the course owner or an admin can update.
     */
    public function update(UpdateCourseRequest $request, Course $course): JsonResponse
    {
        $this->authorize('update', $course);
        $course->update($request->validated());

        return $this->success(
            new CourseResource($course->load('category', 'teacher')),
            'Course updated successfully'
        );
    }

    /**
     * Delete a course (soft delete).
     *
     * Only the course owner or an admin can delete.
     */
    public function destroy(Course $course): JsonResponse
    {
        $this->authorize('delete', $course);
        $course->delete();

        return $this->noContent();
    }

    /**
     * List the authenticated teacher's courses.
     */
    #[\Dedoc\Scramble\Attributes\QueryParameter('per_page', description: 'Items per page (max 100).', type: 'integer', default: 15)]
    #[\Dedoc\Scramble\Attributes\QueryParameter('page', description: 'Page number.', type: 'integer', default: 1)]
    public function myCourses(Request $request): JsonResponse
    {
        $courses = Course::query()
            ->byTeacher($request->user()->id)
            ->with('category')
            ->withCount('lessons', 'enrollments')
            ->latest()
            ->paginate(15);

        return $this->success(CourseResource::collection($courses));
    }

    /**
     * List published courses for a specific category, resolved by slug.
     * Route: GET /categories/{category:slug}/courses
     */
    /**
     * List published courses for a specific category.
     *
     * The category is resolved by its slug (e.g. `web-development`).
     */
    #[\Dedoc\Scramble\Attributes\QueryParameter('search', description: 'Search in title and description.', type: 'string')]
    #[\Dedoc\Scramble\Attributes\QueryParameter('filter[level]', description: 'Filter by level: BEGINNER, INTERMEDIATE, ADVANCED.', type: 'string')]
    #[\Dedoc\Scramble\Attributes\QueryParameter('filter[language]', description: 'Filter by language.', type: 'string')]
    #[\Dedoc\Scramble\Attributes\QueryParameter('sort', description: 'Sort field: title, price, created_at, duration.', type: 'string')]
    #[\Dedoc\Scramble\Attributes\QueryParameter('order', description: 'asc or desc.', type: 'string', default: 'desc')]
    #[\Dedoc\Scramble\Attributes\QueryParameter('per_page', description: 'Items per page (max 100).', type: 'integer', default: 15)]
    public function byCategory(Request $request, \App\Models\Category $category): JsonResponse
    {
        return $this->paginatedResponse(
            query: Course::query()
                ->published()
                ->where('category_id', $category->id)
                ->with('category', 'teacher')
                ->withCount('lessons', 'enrollments'),
            request: $request,
            resourceClass: CourseResource::class,
            searchColumns: ['title', 'description'],
            allowedSorts: ['title', 'price', 'created_at', 'duration'],
            allowedFilters: ['level', 'language'],
        );
    }
}

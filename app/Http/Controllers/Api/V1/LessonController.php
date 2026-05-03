<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\StoreLessonRequest;
use App\Http\Requests\Api\V1\UpdateLessonRequest;
use App\Http\Resources\LessonResource;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LessonController extends ApiController
{
    /**
     * List published lessons for a course.
     *
     * Ordered by section then order. Includes resources.
     */
    #[\Dedoc\Scramble\Attributes\QueryParameter('search', description: 'Search in title.', type: 'string')]
    #[\Dedoc\Scramble\Attributes\QueryParameter('sort', description: 'Sort field: title, order, duration.', type: 'string', example: 'order')]
    #[\Dedoc\Scramble\Attributes\QueryParameter('order', description: 'Sort direction: asc or desc.', type: 'string', example: 'asc')]
    #[\Dedoc\Scramble\Attributes\QueryParameter('per_page', description: 'Items per page (max 100).', type: 'integer', default: 15)]
    #[\Dedoc\Scramble\Attributes\QueryParameter('page', description: 'Page number.', type: 'integer', default: 1)]
    public function index(Request $request, Course $course): JsonResponse
    {
        return $this->paginatedResponse(
            query: $course->lessons()
                ->published()
                ->with('resources'),
            request: $request,
            resourceClass: LessonResource::class,
            searchColumns: ['title'],
            allowedSorts: ['title', 'order', 'duration'],
        );
    }

    /**
     * Create a lesson in a course.
     *
     * Course owner or admin only.
     */
    public function store(StoreLessonRequest $request, Course $course): JsonResponse
    {
        $this->authorize('create', [Lesson::class, $course]);

        $lesson = $course->lessons()->create($request->validated());

        return $this->created(new LessonResource($lesson), 'Lesson created successfully');
    }

    /**
     * Get a single lesson with its resources and assignments.
     */
    public function show(Course $course, Lesson $lesson): JsonResponse
    {
        $lesson->load('resources', 'assignments');

        return $this->success(new LessonResource($lesson));
    }

    public function update(UpdateLessonRequest $request, Course $course, Lesson $lesson): JsonResponse
    {
        $this->authorize('update', $lesson);
        $lesson->update($request->validated());

        return $this->success(new LessonResource($lesson), 'Lesson updated successfully');
    }

    public function destroy(Course $course, Lesson $lesson): JsonResponse
    {
        $this->authorize('delete', $lesson);
        $lesson->delete();

        return $this->noContent();
    }
}

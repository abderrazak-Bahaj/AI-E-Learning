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

final class LessonController extends ApiController
{
    public function index(Course $course): JsonResponse
    {
        $lessons = $course->lessons()
            ->published()
            ->with('resources')
            ->ordered()
            ->get();

        return $this->success(LessonResource::collection($lessons));
    }

    public function store(StoreLessonRequest $request, Course $course): JsonResponse
    {
        $this->authorize('create', [Lesson::class, $course]);

        $lesson = $course->lessons()->create($request->validated());

        return $this->created(new LessonResource($lesson), 'Lesson created successfully');
    }

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

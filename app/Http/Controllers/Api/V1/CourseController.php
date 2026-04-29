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
    public function index(Request $request): JsonResponse
    {
        $search = $request->string('search')->toString();

        $courses = Course::query()
            ->published()
            ->with('category', 'teacher')
            ->withCount('lessons', 'enrollments')
            ->when($request->filled('category'), fn ($q) => $q->whereHas(
                'category', fn ($q) => $q->where('slug', $request->category)
            ))
            ->when($request->filled('level'), fn ($q) => $q->byLevel($request->level))
            ->when($search !== '', fn ($q) => $q->where('title', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15);

        return $this->success(CourseResource::collection($courses));
    }

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

    public function show(Course $course): JsonResponse
    {
        $course->load('category', 'teacher', 'lessons')->loadCount('enrollments');

        return $this->success(new CourseResource($course));
    }

    public function update(UpdateCourseRequest $request, Course $course): JsonResponse
    {
        $this->authorize('update', $course);
        $course->update($request->validated());

        return $this->success(
            new CourseResource($course->load('category', 'teacher')),
            'Course updated successfully'
        );
    }

    public function destroy(Course $course): JsonResponse
    {
        $this->authorize('delete', $course);
        $course->delete();

        return $this->noContent();
    }

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
}

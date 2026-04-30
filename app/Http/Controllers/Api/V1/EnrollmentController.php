<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\StoreEnrollmentRequest;
use App\Http\Resources\EnrollmentResource;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EnrollmentController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $enrollments = Enrollment::query()
            ->forStudent($request->user()->id)
            ->with('course.category', 'certificate')
            ->latest('enrolled_at')
            ->paginate(15);

        return $this->success(EnrollmentResource::collection($enrollments));
    }

    public function store(StoreEnrollmentRequest $request): JsonResponse
    {
        $this->authorize('create', Enrollment::class);

        $course = Course::query()->findOrFail($request->course_id);

        $exists = Enrollment::query()
            ->forStudent($request->user()->id)
            ->forCourse($course->id)
            ->exists();

        if ($exists) {
            return $this->error('Already enrolled in this course', 409);
        }

        $enrollment = Enrollment::query()->create([
            'student_id' => $request->user()->id,
            'course_id' => $course->id,
            'status' => 'ACTIVE',
            'progress' => 0,
        ]);

        $request->user()->notify(new \App\Notifications\EnrollmentConfirmed($enrollment->load('course')));

        return $this->created(
            new EnrollmentResource($enrollment->load('course')),
            'Enrolled successfully'
        );
    }

    public function show(Enrollment $enrollment): JsonResponse
    {
        $this->authorize('view', $enrollment);
        $enrollment->load('course.lessons', 'certificate');

        return $this->success(new EnrollmentResource($enrollment));
    }

    public function destroy(Enrollment $enrollment): JsonResponse
    {
        $this->authorize('delete', $enrollment);
        $enrollment->update(['status' => 'DROPPED']);

        return $this->success(message: 'Enrollment dropped successfully');
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Events\EnrollmentCompleted;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\UpdateLessonProgressRequest;
use App\Http\Resources\LessonProgressResource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LessonProgressController extends ApiController
{
    /**
     * Get the authenticated student's progress for all lessons in a course.
     */
    public function index(Request $request, Course $course): JsonResponse
    {
        $progress = LessonProgress::query()
            ->forStudent($request->user()->id)
            ->forCourse($course->id)
            ->get();

        return $this->success(LessonProgressResource::collection($progress));
    }

    /**
     * Update lesson progress for the authenticated student.
     *
     * When all lessons reach COMPLETED status, the enrollment is automatically
     * marked as completed and a certificate is issued.
     */
    public function update(UpdateLessonProgressRequest $request, Course $course, Lesson $lesson): JsonResponse
    {
        $data = $request->validated();
        $studentId = $request->user()->id;

        $progress = LessonProgress::query()->updateOrCreate(
            ['student_id' => $studentId, 'lesson_id' => $lesson->id],
            array_merge($data, [
                'course_id' => $course->id,
                'started_at' => now(),
                'completed_at' => $data['status'] === 'COMPLETED' ? now() : null,
            ])
        );

        // Check if all lessons in the course are now completed → fire EnrollmentCompleted
        if ($data['status'] === 'COMPLETED') {
            $this->checkCourseCompletion($course, $studentId);
        }

        return $this->success(new LessonProgressResource($progress), 'Progress updated');
    }

    /**
     * Fire EnrollmentCompleted if every published lesson in the course is done.
     */
    private function checkCourseCompletion(Course $course, string $studentId): void
    {
        $totalLessons = $course->lessons()->published()->count();

        if ($totalLessons === 0) {
            return;
        }

        $completedLessons = LessonProgress::query()
            ->where('student_id', $studentId)
            ->where('course_id', $course->id)
            ->where('status', 'COMPLETED')
            ->count();

        if ($completedLessons < $totalLessons) {
            return;
        }

        // All lessons done — mark enrollment as completed and fire event
        $enrollment = Enrollment::query()
            ->where('student_id', $studentId)
            ->where('course_id', $course->id)
            ->where('status', 'ACTIVE')
            ->first();

        if (! $enrollment) {
            return;
        }

        $enrollment->update([
            'status' => 'COMPLETED',
            'progress' => 100,
            'completed_at' => now(),
        ]);

        EnrollmentCompleted::dispatch($enrollment->fresh());
    }
}

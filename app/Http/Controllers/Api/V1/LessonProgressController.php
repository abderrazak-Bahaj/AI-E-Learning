<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\UpdateLessonProgressRequest;
use App\Http\Resources\LessonProgressResource;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LessonProgressController extends ApiController
{
    public function index(Request $request, Course $course): JsonResponse
    {
        $progress = LessonProgress::query()
            ->forStudent($request->user()->id)
            ->forCourse($course->id)
            ->get();

        return $this->success(LessonProgressResource::collection($progress));
    }

    public function update(UpdateLessonProgressRequest $request, Course $course, Lesson $lesson): JsonResponse
    {
        $data = $request->validated();

        $progress = LessonProgress::query()->updateOrCreate(
            ['student_id' => $request->user()->id, 'lesson_id' => $lesson->id],
            array_merge($data, [
                'course_id' => $course->id,
                'started_at' => now(),
                'completed_at' => $data['status'] === 'COMPLETED' ? now() : null,
            ])
        );

        return $this->success(new LessonProgressResource($progress), 'Progress updated');
    }
}

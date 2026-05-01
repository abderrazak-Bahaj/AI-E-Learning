<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Ai\Agents\LessonExplainerAgent;
use App\Http\Controllers\Api\ApiController;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

final class AiController extends ApiController
{
    /**
     * Help a student understand a lesson by answering their question.
     * Only accessible to students enrolled in the course.
     *
     * POST /courses/{course}/lessons/{lesson}/explain
     */
    /**
     * AI: Ask a question about a lesson.
     *
     * The AI tutor explains the lesson content in response to the student's question.
     * Only available to students enrolled in the course.
     * Returns 503 if the AI provider is unavailable.
     */
    public function explainLesson(Request $request, Course $course, Lesson $lesson): JsonResponse
    {
        $request->validate([
            'question' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        // Ensure the student is enrolled in this course
        $isEnrolled = $request->user()->enrollments()
            ->where('course_id', $course->id)
            ->where('status', 'ACTIVE')
            ->exists();

        if (! $isEnrolled && ! $request->user()->isAdmin()) {
            return $this->forbidden('You must be enrolled in this course to use the AI tutor.');
        }

        try {
            $response = (new LessonExplainerAgent(
                lesson: $lesson,
                studentQuestion: $request->string('question')->toString(),
            ))->prompt($request->string('question')->toString());

            return $this->success([
                'explanation' => (string) $response,
                'lesson_id' => $lesson->id,
                'lesson_title' => $lesson->title,
            ], 'Here is your explanation.');

        } catch (Throwable $e) {
            return $this->error(
                'AI service is temporarily unavailable. Please try again later.',
                503
            );
        }
    }
}

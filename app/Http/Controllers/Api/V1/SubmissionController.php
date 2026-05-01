<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Ai\Agents\EssayGraderAgent;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\GradeSubmissionRequest;
use App\Http\Requests\Api\V1\StoreSubmissionRequest;
use App\Http\Resources\SubmissionResource;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\SubmissionAnswer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

final class SubmissionController extends ApiController
{
    /**
     * List submissions for an assignment.
     *
     * Students see only their own submissions. Teachers/admins see all.
     */
    public function index(Request $request, Course $course, Assignment $assignment): JsonResponse
    {
        $query = $assignment->submissions()->with('student');

        if ($request->user()->isStudent()) {
            $query->forStudent($request->user()->id);
        }

        return $this->success(SubmissionResource::collection($query->latest()->get()));
    }

    /**
     * Submit answers to an assignment.
     *
     * Multiple-choice answers are auto-graded immediately.
     * Essay answers require manual grading via PATCH /grade.
     */
    public function store(StoreSubmissionRequest $request, Course $course, Assignment $assignment): JsonResponse
    {
        $this->authorize('create', Submission::class);

        $attemptCount = $assignment->submissions()
            ->forStudent($request->user()->id)
            ->count();

        if ($attemptCount >= $assignment->max_attempts) {
            return $this->error('Maximum attempts reached', 422);
        }

        $submission = Submission::query()->create([
            'student_id' => $request->user()->id,
            'assignment_id' => $assignment->id,
            'attempt_number' => $attemptCount + 1,
            'status' => 'SUBMITTED',
            'submitted_at' => now(),
        ]);

        foreach ($request->answers as $answer) {
            SubmissionAnswer::query()->create([
                'submission_id' => $submission->id,
                'question_id' => $answer['question_id'],
                'selected_option_id' => $answer['selected_option_id'] ?? null,
                'answer' => $answer['answer'] ?? null,
            ]);
        }

        $this->autoGrade($submission);

        return $this->created(
            new SubmissionResource($submission->load('answers')),
            'Submission received'
        );
    }

    public function show(Course $course, Assignment $assignment, Submission $submission): JsonResponse
    {
        $this->authorize('view', $submission);
        $submission->load('answers.question.options', 'answers.selectedOption', 'student');

        return $this->success(new SubmissionResource($submission));
    }

    /**
     * Grade a submission.
     *
     * Teacher or admin only. Sets score, feedback, and marks is_passed.
     */
    public function grade(GradeSubmissionRequest $request, Course $course, Assignment $assignment, Submission $submission): JsonResponse
    {
        $this->authorize('grade', $submission);

        $submission->update([
            'score' => $request->score,
            'feedback' => $request->feedback,
            'status' => 'GRADED',
            'is_passed' => $request->score >= $assignment->passing_score,
        ]);

        foreach ($request->answers ?? [] as $answerData) {
            SubmissionAnswer::query()->where('id', $answerData['id'])->update([
                'score' => $answerData['score'],
                'feedback' => $answerData['feedback'] ?? null,
            ]);
        }

        return $this->success(new SubmissionResource($submission->fresh()), 'Submission graded');
    }

    /**
     * AI pre-grading for essay submissions.
     * Returns a score suggestion — does NOT save. Teacher must confirm via grade().
     *
     * POST /courses/{course}/assignments/{assignment}/submissions/{submission}/pre-grade
     */
    /**
     * AI: Pre-grade an essay submission.
     *
     * Returns AI score suggestion — does NOT save. Teacher confirms via PATCH /grade.
     * Teacher or admin only.
     */
    public function preGrade(Course $course, Assignment $assignment, Submission $submission): JsonResponse
    {
        $this->authorize('grade', $submission);

        // Only essay-type assignments make sense for AI pre-grading
        if (! in_array($assignment->type, ['ESSAY', 'QUIZ'], true)) {
            return $this->error('AI pre-grading is only available for essay and quiz assignments.', 422);
        }

        // Collect essay answers from the submission
        $submission->loadMissing('answers.question');
        $essayAnswers = $submission->answers
            ->filter(fn ($a) => in_array($a->question?->question_type, ['ESSAY', 'SHORT_ANSWER'], true))
            ->values();

        if ($essayAnswers->isEmpty()) {
            return $this->error('No essay answers found in this submission to pre-grade.', 422);
        }

        try {
            $suggestions = [];

            foreach ($essayAnswers as $answer) {
                $agent = new EssayGraderAgent(
                    question: $answer->question->question_text,
                    answer: $answer->answer ?? '',
                    maxPoints: $answer->question->points,
                );

                $result = $agent->prompt($agent->buildPrompt());

                $suggestions[] = [
                    'answer_id' => $answer->id,
                    'question_id' => $answer->question_id,
                    'question_text' => $answer->question->question_text,
                    'suggested_score' => $result['score'],
                    'feedback' => $result['feedback'],
                    'confidence' => $result['confidence'],
                    'strengths' => $result['strengths'] ?? [],
                    'improvements' => $result['improvements'] ?? [],
                    'max_points' => $answer->question->points,
                ];
            }

            return $this->success([
                'submission_id' => $submission->id,
                'suggestions' => $suggestions,
                'note' => 'These are AI suggestions only. Use the grade endpoint to confirm or override.',
            ], 'AI pre-grading complete.');

        } catch (Throwable $e) {
            return $this->error('AI grading service is temporarily unavailable.', 503);
        }
    }

    private function autoGrade(Submission $submission): void
    {
        $totalScore = 0;
        $totalPoints = 0;

        foreach ($submission->answers()->with('question.options', 'selectedOption')->get() as $answer) {
            $question = $answer->question;
            $totalPoints += $question->points;

            if ($question->isChoiceBased() && $answer->selectedOption) {
                $isCorrect = $answer->selectedOption->is_correct;
                $score = $isCorrect ? $question->points : 0;
                $answer->update(['is_correct' => $isCorrect, 'score' => $score]);
                $totalScore += $score;
            }
        }

        if ($totalPoints > 0) {
            $percentage = ($totalScore / $totalPoints) * $submission->assignment->total_points;
            $submission->update([
                'score' => round($percentage, 2),
                'is_passed' => $percentage >= $submission->assignment->passing_score,
                'status' => 'GRADED',
            ]);
        }
    }
}

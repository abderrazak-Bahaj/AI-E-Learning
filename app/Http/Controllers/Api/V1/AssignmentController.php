<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Ai\Agents\AssignmentGeneratorAgent;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\StoreAssignmentRequest;
use App\Http\Requests\Api\V1\StoreQuestionRequest;
use App\Http\Requests\Api\V1\UpdateAssignmentRequest;
use App\Http\Resources\AssignmentQuestionResource;
use App\Http\Resources\AssignmentResource;
use App\Models\Assignment;
use App\Models\AssignmentOption;
use App\Models\AssignmentQuestion;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

final class AssignmentController extends ApiController
{
    public function index(Course $course): JsonResponse
    {
        $assignments = $course->assignments()
            ->published()
            ->withCount('questions')
            ->get();

        return $this->success(AssignmentResource::collection($assignments));
    }

    public function store(StoreAssignmentRequest $request, Course $course): JsonResponse
    {
        $this->authorize('create', Assignment::class);

        $assignment = $course->assignments()->create($request->validated());

        return $this->created(new AssignmentResource($assignment), 'Assignment created successfully');
    }

    public function show(Course $course, Assignment $assignment): JsonResponse
    {
        $assignment->load('questions.options');

        return $this->success(new AssignmentResource($assignment));
    }

    public function update(UpdateAssignmentRequest $request, Course $course, Assignment $assignment): JsonResponse
    {
        $this->authorize('update', $assignment);
        $assignment->update($request->validated());

        return $this->success(new AssignmentResource($assignment), 'Assignment updated successfully');
    }

    public function destroy(Course $course, Assignment $assignment): JsonResponse
    {
        $this->authorize('delete', $assignment);
        $assignment->delete();

        return $this->noContent();
    }

    public function storeQuestion(StoreQuestionRequest $request, Course $course, Assignment $assignment): JsonResponse
    {
        $this->authorize('update', $assignment);

        $data = $request->validated();
        $options = $data['options'] ?? [];
        unset($data['options']);

        $question = $assignment->questions()->create($data);

        foreach ($options as $index => $option) {
            AssignmentOption::query()->create([
                'question_id' => $question->id,
                'option_text' => $option['option_text'],
                'is_correct' => $option['is_correct'],
                'order' => $option['order'] ?? $index + 1,
            ]);
        }

        return $this->created(
            new AssignmentQuestionResource($question->load('options')),
            'Question added successfully'
        );
    }

    public function destroyQuestion(Course $course, Assignment $assignment, AssignmentQuestion $question): JsonResponse
    {
        $this->authorize('update', $assignment);
        $question->delete();

        return $this->noContent();
    }

    /**
     * AI-generated assignment draft for the teacher to review.
     * Does NOT save — teacher calls store() to confirm.
     *
     * POST /courses/{course}/assignments/generate
     */
    public function generate(Request $request, Course $course): JsonResponse
    {
        $this->authorize('create', Assignment::class);

        $request->validate([
            'type' => ['required', 'in:QUIZ,ESSAY,MULTIPLE_CHOICE,TRUE_FALSE'],
            'question_count' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        try {
            $agent = new AssignmentGeneratorAgent(
                course: $course,
                type: $request->string('type')->toString(),
                questionCount: $request->integer('question_count', 5),
            );

            $result = $agent->prompt($agent->buildPrompt());

            return $this->success([
                'draft' => [
                    'title' => $result['title'],
                    'description' => $result['description'],
                    'type' => $request->string('type')->toString(),
                    'passing_score' => $result['passing_score'],
                    'total_points' => collect($result['questions'])->sum('points'),
                    'questions' => $result['questions'],
                ],
                'note' => 'Review and edit this draft, then call POST /assignments to save it.',
            ], 'Assignment draft generated successfully.');

        } catch (Throwable $e) {
            return $this->error('AI service is temporarily unavailable. Please try again later.', 503);
        }
    }
}

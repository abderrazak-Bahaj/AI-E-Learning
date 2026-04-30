<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Models\Course;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Generates a complete assignment draft for a teacher to review and save.
 * Returns structured data matching the StoreAssignmentRequest + StoreQuestionRequest format.
 */
#[Temperature(0.7)]
final class AssignmentGeneratorAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function __construct(
        private readonly Course $course,
        private readonly string $type,
        private readonly int $questionCount = 5,
    ) {}

    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
        You are an expert curriculum designer and educator. Your job is to create high-quality,
        pedagogically sound assignments for online courses.

        Rules:
        - Questions must directly relate to the course content.
        - For MULTIPLE_CHOICE: provide exactly 4 options, exactly 1 correct.
        - For TRUE_FALSE: provide exactly 2 options (True/False), mark the correct one.
        - For ESSAY: provide a clear, open-ended question with a model answer hint.
        - Difficulty should be appropriate for the course level.
        - Questions should test understanding, not just memorization.
        PROMPT;
    }

    /** @return array<string, mixed> */
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->description('Assignment title')->required(),
            'description' => $schema->string()->description('Clear instructions for students')->required(),
            'passing_score' => $schema->integer()->minimum(50)->maximum(90)->description('Minimum score to pass (%)')->required(),
            'questions' => $schema->array()
                ->items($schema->object(fn (JsonSchema $s) => [
                    'question_text' => $s->string()->required(),
                    'question_type' => $s->string()->enum(['MULTIPLE_CHOICE', 'TRUE_FALSE', 'SHORT_ANSWER', 'ESSAY'])->required(),
                    'points' => $s->integer()->minimum(1)->maximum(20)->required(),
                    'explanation' => $s->string()->description('Shown after answering'),
                    'options' => $s->array()->items($s->object(fn (JsonSchema $o) => [
                        'option_text' => $o->string()->required(),
                        'is_correct' => $o->boolean()->required(),
                    ])),
                ]))
                ->description("Exactly {$this->questionCount} questions")
                ->required(),
        ];
    }

    public function buildPrompt(): string
    {
        return <<<PROMPT
        Course: {$this->course->title}
        Level: {$this->course->level}
        Description: {$this->course->description}
        Assignment Type: {$this->type}
        Number of Questions: {$this->questionCount}

        Generate a complete {$this->type} assignment for this course with exactly {$this->questionCount} questions.
        PROMPT;
    }
}

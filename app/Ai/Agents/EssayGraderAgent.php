<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Pre-grades an essay submission and returns a score suggestion with feedback.
 * The teacher must confirm or override before the grade is saved.
 */
#[Temperature(0.3)]
final class EssayGraderAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function __construct(
        private readonly string $question,
        private readonly string $answer,
        private readonly int $maxPoints,
    ) {}

    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
        You are an expert academic grader. Your job is to evaluate a student's essay answer
        against the question asked and provide a fair, constructive grade.

        Rules:
        - Be objective and consistent.
        - Award partial credit where appropriate.
        - Keep feedback concise (2–4 sentences), actionable, and encouraging.
        - Never award more than the maximum points.
        - Confidence reflects how clear-cut the grading is (1.0 = obvious, 0.5 = borderline).
        PROMPT;
    }

    /** @return array<string, mixed> */
    public function schema(JsonSchema $schema): array
    {
        return [
            'score' => $schema->number()
                ->minimum(0)
                ->maximum($this->maxPoints)
                ->description("Points awarded out of {$this->maxPoints}")
                ->required(),
            'feedback' => $schema->string()
                ->description('Constructive feedback for the student')
                ->required(),
            'confidence' => $schema->number()
                ->minimum(0)
                ->maximum(1)
                ->description('How confident the grader is (0.0–1.0)')
                ->required(),
            'strengths' => $schema->array()
                ->items($schema->string())
                ->description('Key strengths in the answer'),
            'improvements' => $schema->array()
                ->items($schema->string())
                ->description('Specific areas to improve'),
        ];
    }

    public function buildPrompt(): string
    {
        return <<<PROMPT
        Question: {$this->question}

        Student Answer:
        {$this->answer}

        Maximum Points: {$this->maxPoints}

        Please evaluate this answer and provide a grade with feedback.
        PROMPT;
    }
}

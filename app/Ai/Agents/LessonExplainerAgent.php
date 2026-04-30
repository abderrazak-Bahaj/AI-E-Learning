<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Models\Lesson;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Helps a student understand a lesson by answering their specific question
 * in the context of the lesson content.
 */
#[Temperature(0.5)]
final class LessonExplainerAgent implements Agent
{
    use Promptable;

    public function __construct(
        private readonly Lesson $lesson,
        private readonly string $studentQuestion,
    ) {}

    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
        You are a patient, knowledgeable tutor helping a student understand course material.

        Rules:
        - Answer only questions related to the lesson content provided.
        - Use simple, clear language appropriate for a learner.
        - Provide concrete examples when helpful.
        - If the question is unrelated to the lesson, politely redirect the student.
        - Keep responses focused and under 300 words unless a longer explanation is truly needed.
        - Never do the student's homework for them — guide, don't solve.
        PROMPT;
    }

    public function buildPrompt(): string
    {
        $content = mb_substr($this->lesson->content, 0, 2000);

        return <<<PROMPT
        Lesson Title: {$this->lesson->title}

        Lesson Content:
        {$content}

        Student's Question:
        {$this->studentQuestion}

        Please explain this concept to the student in a clear, helpful way.
        PROMPT;
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AssignmentOption;
use App\Models\AssignmentQuestion;
use App\Models\Submission;
use App\Models\SubmissionAnswer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubmissionAnswer>
 */
final class SubmissionAnswerFactory extends Factory
{
    protected $model = SubmissionAnswer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'submission_id' => Submission::factory(),
            'question_id' => AssignmentQuestion::factory(),
            'selected_option_id' => null,
            'answer' => null,
            'is_correct' => null,
            'score' => null,
            'feedback' => null,
        ];
    }

    public function withAnswer(string $answer): static
    {
        return $this->state(fn (array $attributes): array => [
            'answer' => $answer,
            'selected_option_id' => null,
        ]);
    }

    public function withOption(AssignmentOption $option): static
    {
        return $this->state(fn (array $attributes): array => [
            'selected_option_id' => $option->id,
            'is_correct' => $option->is_correct,
            'answer' => null,
        ]);
    }

    public function forSubmission(Submission $submission): static
    {
        return $this->state(fn (array $attributes): array => [
            'submission_id' => $submission->id,
        ]);
    }

    public function forQuestion(AssignmentQuestion $question): static
    {
        return $this->state(fn (array $attributes): array => [
            'question_id' => $question->id,
        ]);
    }
}

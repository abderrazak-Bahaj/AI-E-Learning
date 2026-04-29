<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\AssignmentQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssignmentQuestion>
 */
final class AssignmentQuestionFactory extends Factory
{
    protected $model = AssignmentQuestion::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question_text' => fake()->sentence().'?',
            'question_type' => fake()->randomElement(['MULTIPLE_CHOICE', 'TRUE_FALSE', 'SHORT_ANSWER', 'ESSAY']),
            'points' => fake()->randomElement([5, 10, 15, 20]),
            'order' => fake()->numberBetween(1, 20),
            'explanation' => fake()->optional(0.6)->sentence(),
            'assignment_id' => Assignment::factory(),
        ];
    }

    public function multipleChoice(): static
    {
        return $this->state(fn (array $attributes): array => [
            'question_type' => 'MULTIPLE_CHOICE',
        ]);
    }

    public function trueFalse(): static
    {
        return $this->state(fn (array $attributes): array => [
            'question_type' => 'TRUE_FALSE',
        ]);
    }

    public function essay(): static
    {
        return $this->state(fn (array $attributes): array => [
            'question_type' => 'ESSAY',
            'points' => 20,
        ]);
    }

    public function forAssignment(Assignment $assignment): static
    {
        return $this->state(fn (array $attributes): array => [
            'assignment_id' => $assignment->id,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AssignmentOption;
use App\Models\AssignmentQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssignmentOption>
 */
final class AssignmentOptionFactory extends Factory
{
    protected $model = AssignmentOption::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'option_text' => fake()->sentence(3),
            'is_correct' => false,
            'order' => fake()->numberBetween(1, 4),
            'question_id' => AssignmentQuestion::factory()->multipleChoice(),
        ];
    }

    public function correct(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_correct' => true,
        ]);
    }

    public function forQuestion(AssignmentQuestion $question): static
    {
        return $this->state(fn (array $attributes): array => [
            'question_id' => $question->id,
        ]);
    }
}

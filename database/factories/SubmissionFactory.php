<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Submission>
 */
final class SubmissionFactory extends Factory
{
    protected $model = Submission::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => User::factory()->student(),
            'assignment_id' => Assignment::factory(),
            'attempt_number' => 1,
            'score' => null,
            'is_passed' => false,
            'feedback' => null,
            'status' => 'SUBMITTED',
            'submitted_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }

    public function graded(): static
    {
        return $this->state(function (array $attributes): array {
            $score = fake()->randomFloat(1, 40, 100);

            return [
                'status' => 'GRADED',
                'score' => $score,
                'is_passed' => $score >= 60,
                'feedback' => fake()->paragraph(),
            ];
        });
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'IN_PROGRESS',
            'submitted_at' => null,
        ]);
    }

    public function forStudent(User $student): static
    {
        return $this->state(fn (array $attributes): array => [
            'student_id' => $student->id,
        ]);
    }

    public function forAssignment(Assignment $assignment): static
    {
        return $this->state(fn (array $attributes): array => [
            'assignment_id' => $assignment->id,
        ]);
    }
}

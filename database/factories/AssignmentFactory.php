<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assignment>
 */
final class AssignmentFactory extends Factory
{
    protected $model = Assignment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(3),
            'type' => fake()->randomElement(['QUIZ', 'ESSAY', 'MULTIPLE_CHOICE', 'TRUE_FALSE']),
            'time_limit' => fake()->optional(0.7)->randomElement([15, 30, 45, 60, 90, 120]),
            'max_attempts' => fake()->randomElement([1, 2, 3]),
            'total_points' => fake()->randomElement([50, 100]),
            'passing_score' => fake()->randomElement([50, 60, 70]),
            'status' => 'PUBLISHED',
            'course_id' => Course::factory(),
            'lesson_id' => null,
        ];
    }

    public function quiz(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'QUIZ',
            'time_limit' => 30,
            'total_points' => 50,
        ]);
    }

    public function essay(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'ESSAY',
            'time_limit' => null,
            'total_points' => 100,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'DRAFT',
        ]);
    }

    public function forCourse(Course $course): static
    {
        return $this->state(fn (array $attributes): array => [
            'course_id' => $course->id,
        ]);
    }

    public function forLesson(Lesson $lesson): static
    {
        return $this->state(fn (array $attributes): array => [
            'course_id' => $lesson->course_id,
            'lesson_id' => $lesson->id,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enrollment>
 */
final class EnrollmentFactory extends Factory
{
    protected $model = Enrollment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $enrolledAt = fake()->dateTimeBetween('-1 year', 'now');

        return [
            'student_id' => User::factory()->student(),
            'course_id' => Course::factory(),
            'status' => 'ACTIVE',
            'progress' => fake()->randomFloat(2, 0, 100),
            'enrolled_at' => $enrolledAt,
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(function (array $attributes): array {
            $enrolledAt = $attributes['enrolled_at'] ?? now()->subMonths(3);

            return [
                'status' => 'COMPLETED',
                'progress' => 100,
                'completed_at' => fake()->dateTimeBetween($enrolledAt, 'now'),
            ];
        });
    }

    public function dropped(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'DROPPED',
        ]);
    }

    public function forStudent(User $student): static
    {
        return $this->state(fn (array $attributes): array => [
            'student_id' => $student->id,
        ]);
    }

    public function forCourse(Course $course): static
    {
        return $this->state(fn (array $attributes): array => [
            'course_id' => $course->id,
        ]);
    }
}

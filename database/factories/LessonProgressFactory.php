<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LessonProgress>
 */
final class LessonProgressFactory extends Factory
{
    protected $model = LessonProgress::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $lesson = Lesson::inRandomOrder()->first() ?? Lesson::factory()->create();
        $startedAt = fake()->dateTimeBetween('-1 year', 'now');

        return [
            'student_id' => User::factory()->student(),
            'lesson_id' => $lesson->id,
            'course_id' => $lesson->course_id,
            'status' => 'IN_PROGRESS',
            'watch_time' => fake()->numberBetween(0, $lesson->duration * 60),
            'last_position' => fake()->numberBetween(0, $lesson->duration * 60),
            'started_at' => $startedAt,
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(function (array $attributes): array {
            $startedAt = $attributes['started_at'] ?? now()->subHours(2);

            return [
                'status' => 'COMPLETED',
                'completed_at' => fake()->dateTimeBetween($startedAt, 'now'),
            ];
        });
    }

    public function notStarted(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'NOT_STARTED',
            'watch_time' => 0,
            'last_position' => 0,
            'started_at' => null,
            'completed_at' => null,
        ]);
    }

    public function forStudent(User $student): static
    {
        return $this->state(fn (array $attributes): array => [
            'student_id' => $student->id,
        ]);
    }

    public function forLesson(Lesson $lesson): static
    {
        return $this->state(fn (array $attributes): array => [
            'lesson_id' => $lesson->id,
            'course_id' => $lesson->course_id,
        ]);
    }
}

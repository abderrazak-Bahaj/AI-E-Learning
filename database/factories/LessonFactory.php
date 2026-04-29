<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lesson>
 */
final class LessonFactory extends Factory
{
    protected $model = Lesson::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'content' => fake()->paragraphs(4, true),
            'video_url' => fake()->optional(0.8)->url(),
            'order' => fake()->numberBetween(1, 20),
            'section' => fake()->numberBetween(1, 5),
            'duration' => fake()->numberBetween(10, 120),
            'is_free_preview' => fake()->boolean(20),
            'status' => 'PUBLISHED',
            'course_id' => Course::factory(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'DRAFT',
        ]);
    }

    public function freePreview(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_free_preview' => true,
        ]);
    }

    public function forCourse(Course $course): static
    {
        return $this->state(fn (array $attributes): array => [
            'course_id' => $course->id,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<resource>
 */
final class ResourceFactory extends Factory
{
    protected $model = Resource::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['PDF', 'VIDEO', 'AUDIO', 'LINK', 'OTHER']);

        return [
            'title' => fake()->sentence(3),
            'file_url' => fake()->url(),
            'order' => fake()->numberBetween(1, 10),
            'type' => $type,
            'is_preview' => fake()->boolean(15),
            'course_id' => Course::factory(),
            'lesson_id' => null,
        ];
    }

    public function pdf(): static
    {
        return $this->state(fn (array $attributes): array => ['type' => 'PDF']);
    }

    public function video(): static
    {
        return $this->state(fn (array $attributes): array => ['type' => 'VIDEO']);
    }

    public function link(): static
    {
        return $this->state(fn (array $attributes): array => ['type' => 'LINK']);
    }

    public function forLesson(Lesson $lesson): static
    {
        return $this->state(fn (array $attributes): array => [
            'course_id' => $lesson->course_id,
            'lesson_id' => $lesson->id,
        ]);
    }

    public function forCourse(Course $course): static
    {
        return $this->state(fn (array $attributes): array => [
            'course_id' => $course->id,
            'lesson_id' => null,
        ]);
    }
}

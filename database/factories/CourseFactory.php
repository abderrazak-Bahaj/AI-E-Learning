<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
final class CourseFactory extends Factory
{
    protected $model = Course::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->catchPhrase();

        return [
            'title' => $title,
            'description' => fake()->paragraphs(3, true),
            'image_url' => fake()->optional()->imageUrl(640, 360, 'education'),
            'price' => fake()->randomElement([0, 29.99, 49.99, 79.99, 99.99, 129.99]),
            'status' => 'PUBLISHED',
            'level' => fake()->randomElement(['BEGINNER', 'INTERMEDIATE', 'ADVANCED']),
            'skills' => fake()->randomElements(
                ['HTML', 'CSS', 'JavaScript', 'Python', 'SQL', 'React', 'Laravel', 'Design', 'Marketing'],
                fake()->numberBetween(3, 6)
            ),
            'language' => fake()->randomElement(['English', 'French', 'Spanish', 'Arabic']),
            'duration' => fake()->numberBetween(300, 3000),
            'category_id' => Category::inRandomOrder()->value('id') ?? Category::factory(),
            'teacher_id' => User::where('role', 'teacher')->inRandomOrder()->value('id') ?? User::factory()->teacher(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'DRAFT',
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'ARCHIVED',
        ]);
    }

    public function free(): static
    {
        return $this->state(fn (array $attributes): array => [
            'price' => 0,
        ]);
    }

    public function forCategory(Category $category): static
    {
        return $this->state(fn (array $attributes): array => [
            'category_id' => $category->id,
        ]);
    }

    public function forTeacher(User $teacher): static
    {
        return $this->state(fn (array $attributes): array => [
            'teacher_id' => $teacher->id,
        ]);
    }
}

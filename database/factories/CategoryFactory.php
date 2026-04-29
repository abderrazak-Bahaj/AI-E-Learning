<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
final class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /** @var array<int, array<string, mixed>> */
    private static array $predefined = [
        ['name' => 'Web Development', 'icon' => 'code', 'order' => 1],
        ['name' => 'Data Science', 'icon' => 'chart-bar', 'order' => 2],
        ['name' => 'Business', 'icon' => 'briefcase', 'order' => 3],
        ['name' => 'Design', 'icon' => 'paint-brush', 'order' => 4],
        ['name' => 'Marketing', 'icon' => 'bullhorn', 'order' => 5],
        ['name' => 'Photography', 'icon' => 'camera', 'order' => 6],
        ['name' => 'Health & Fitness', 'icon' => 'heart', 'order' => 7],
        ['name' => 'Music', 'icon' => 'music', 'order' => 8],
        ['name' => 'Personal Development', 'icon' => 'user', 'order' => 9],
        ['name' => 'IT & Software', 'icon' => 'laptop', 'order' => 10],
        ['name' => 'Language Learning', 'icon' => 'language', 'order' => 11],
        ['name' => 'Teaching & Academics', 'icon' => 'chalkboard-teacher', 'order' => 12],
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'parent_id' => null,
            'icon' => fake()->randomElement(['code', 'book', 'star', 'heart', 'music', 'camera']),
            'order' => fake()->numberBetween(1, 100),
            'status' => 'ACTIVE',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'INACTIVE',
        ]);
    }

    public function withParent(Category $parent): static
    {
        return $this->state(fn (array $attributes): array => [
            'parent_id' => $parent->id,
        ]);
    }

    /**
     * Create all predefined categories, skipping existing ones.
     */
    public function createPredefined(): void
    {
        foreach (self::$predefined as $data) {
            Category::firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name' => $data['name'],
                    'description' => 'Learn '.mb_strtolower($data['name']).' skills and techniques.',
                    'parent_id' => null,
                    'icon' => $data['icon'],
                    'order' => $data['order'],
                    'status' => 'ACTIVE',
                ]
            );
        }
    }
}

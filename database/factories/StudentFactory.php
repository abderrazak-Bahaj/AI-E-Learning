<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
final class StudentFactory extends Factory
{
    protected $model = Student::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->student(),
            'student_id' => 'ST'.mb_str_pad((string) fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'enrollment_status' => 'ACTIVE',
            'education_level' => fake()->randomElement([
                'High School',
                'Undergraduate',
                'Graduate',
                'Postgraduate',
                'Professional',
            ]),
            'major' => fake()->randomElement([
                'Computer Science',
                'Data Science',
                'Business Administration',
                'Design',
                'Marketing',
                'Engineering',
            ]),
            'interests' => fake()->randomElements(
                ['Web Development', 'Mobile Apps', 'Data Science', 'AI', 'Design', 'Business', 'Marketing'],
                fake()->numberBetween(2, 4)
            ),
            'date_of_birth' => fake()->dateTimeBetween('-35 years', '-18 years')->format('Y-m-d'),
            'learning_preferences' => fake()->randomElements(
                ['Video', 'Text', 'Interactive', 'Fast', 'Slow', 'Self-paced'],
                fake()->numberBetween(1, 3)
            ),
            'gpa' => fake()->optional(0.7)->randomFloat(2, 2.0, 4.0),
        ];
    }

    public function graduated(): static
    {
        return $this->state(fn (array $attributes): array => [
            'enrollment_status' => 'GRADUATED',
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Teacher>
 */
final class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->teacher(),
            'specialization' => fake()->randomElement([
                'Web Development',
                'Data Science',
                'Machine Learning',
                'Mobile Development',
                'Cloud Computing',
                'Cybersecurity',
                'UI/UX Design',
                'Business Analytics',
            ]),
            'qualification' => fake()->randomElement([
                'PhD in Computer Science',
                'MSc in Data Science',
                'MBA',
                'BSc in Software Engineering',
                'MSc in Artificial Intelligence',
            ]),
            'expertise' => fake()->sentence(6),
            'education' => [
                [
                    'degree' => fake()->randomElement(['PhD', 'MSc', 'MBA', 'BSc']),
                    'field' => fake()->randomElement(['Computer Science', 'Data Science', 'Business', 'Engineering']),
                    'institution' => fake()->company().' University',
                    'year' => fake()->numberBetween(2000, 2020),
                ],
            ],
            'certifications' => [
                [
                    'name' => fake()->randomElement([
                        'AWS Certified Developer',
                        'Google Cloud Professional',
                        'Microsoft Azure Architect',
                        'Certified Scrum Master',
                        'PMP Certification',
                    ]),
                    'year' => fake()->numberBetween(2018, 2024),
                ],
            ],
            'rating' => fake()->randomFloat(2, 3.5, 5.0),
            'years_of_experience' => fake()->numberBetween(2, 20),
        ];
    }
}

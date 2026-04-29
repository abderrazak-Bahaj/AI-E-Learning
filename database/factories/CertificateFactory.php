<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Certificate>
 */
final class CertificateFactory extends Factory
{
    protected $model = Certificate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'certificate_number' => 'CERT-'.mb_strtoupper(Str::random(10)),
            'student_id' => User::factory()->student(),
            'course_id' => Course::factory(),
            'enrollment_id' => null,
            'status' => 'GENERATED',
            'certificate_url' => fake()->url(),
            'template_url' => null,
            'metadata' => ['grade' => 'PASS'],
            'issue_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'generated_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'PENDING',
            'certificate_url' => null,
            'generated_at' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'FAILED',
            'certificate_url' => null,
        ]);
    }

    public function forEnrollment(Enrollment $enrollment): static
    {
        return $this->state(fn (array $attributes): array => [
            'student_id' => $enrollment->student_id,
            'course_id' => $enrollment->course_id,
            'enrollment_id' => $enrollment->id,
            'metadata' => [
                'grade' => 'PASS',
                'completion_date' => $enrollment->completed_at?->format('Y-m-d'),
            ],
        ]);
    }
}

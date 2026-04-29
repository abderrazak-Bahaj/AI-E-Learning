<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Admin>
 */
final class AdminFactory extends Factory
{
    protected $model = Admin::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->admin(),
            'department' => fake()->randomElement([
                'IT Department',
                'Academic Affairs',
                'Student Services',
                'Finance',
                'Operations',
            ]),
            'position' => fake()->randomElement([
                'System Administrator',
                'Platform Manager',
                'Content Manager',
                'Support Lead',
            ]),
            'permissions' => ['manage_users', 'manage_courses', 'manage_settings'],
            'super_admin' => false,
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes): array => [
            'super_admin' => true,
            'permissions' => ['manage_users', 'manage_courses', 'manage_settings', 'manage_payments', 'manage_admins'],
        ]);
    }
}

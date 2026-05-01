<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    private static ?string $password = null;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => self::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'status' => 'ACTIVE',
            'avatar' => null,
            'phone' => fake()->optional()->phoneNumber(),
            'address' => fake()->optional()->address(),
            'bio' => fake()->optional()->sentence(),
            'last_login_at' => fake()->optional()->dateTimeThisYear(),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Assign the 'admin' role after creation.
     */
    public function admin(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->assignRole('admin');
        });
    }

    /**
     * Assign the 'teacher' role after creation.
     */
    public function teacher(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->assignRole('teacher');
        });
    }

    /**
     * Assign the 'student' role after creation.
     */
    public function student(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->assignRole('student');
        });
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'SUSPENDED',
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

final class FixUserRolesSeeder extends Seeder
{
    public function run(): void
    {
        // Assign student role to all users without any role
        $users = User::query()
            ->whereDoesntHave('roles')
            ->get();

        $this->command->info("Found {$users->count()} users without roles");

        foreach ($users as $user) {
            $user->assignRole('student');
            $this->command->line("✓ Assigned student role to {$user->email}");
        }

        $this->command->info("Successfully assigned student role to {$users->count()} users");
    }
}

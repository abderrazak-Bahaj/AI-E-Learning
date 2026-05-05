<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

final class AssignStudentRoleToUsers extends Command
{
    protected $signature = 'users:assign-student-role {--all : Assign to all users without a role}';

    protected $description = 'Assign the student role to users who don\'t have any role';

    public function handle(): int
    {
        if ($this->option('all')) {
            // Get all users without any role
            $users = User::query()
                ->whereDoesntHave('roles')
                ->get();

            $this->info("Found {$users->count()} users without roles");

            foreach ($users as $user) {
                $user->assignRole('student');
                $this->line("✓ Assigned student role to {$user->email}");
            }

            $this->info("Successfully assigned student role to {$users->count()} users");

            return self::SUCCESS;
        }

        // Interactive mode - ask which user
        $email = $this->ask('Enter user email');
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $this->error("User with email {$email} not found");

            return self::FAILURE;
        }

        if ($user->hasRole('student')) {
            $this->info("User {$email} already has the student role");

            return self::SUCCESS;
        }

        $user->assignRole('student');
        $this->info("✓ Successfully assigned student role to {$email}");

        return self::SUCCESS;
    }
}

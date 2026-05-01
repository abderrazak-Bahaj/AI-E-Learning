<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

final class RoleSeeder extends Seeder
{
    /** @var array<string> */
    private array $roles = ['admin', 'teacher', 'student'];

    public function run(): void
    {
        // Create roles for both guards:
        // - 'api'  → used by Passport-authenticated requests
        // - 'web'  → default guard used by assignRole() / syncRoles() without explicit guard
        foreach ($this->roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'api']);
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $this->command->info('Seeded: roles (api + web guards) — '.implode(', ', $this->roles));
    }
}

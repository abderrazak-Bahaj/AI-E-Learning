<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Passport\ClientRepository;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Passport: create personal access client (wiped by RefreshDatabase)
        app(ClientRepository::class)->createPersonalAccessGrantClient(
            'Test Personal Access Client',
        );

        // Spatie: seed roles for both guards (wiped by RefreshDatabase).
        // 'web' is the default guard used by assignRole() without explicit guard.
        // 'api' is used by Passport-authenticated requests.
        foreach (['admin', 'teacher', 'student'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'api']);
        }

        // Clear Spatie's permission cache so fresh roles are picked up
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Passport\Passport;

uses(LazilyRefreshDatabase::class);

describe('User Management', function (): void {
    it('admin can list all users', function (): void {
        $admin = User::factory()->admin()->create();
        User::factory(5)->create();
        Passport::actingAs($admin);

        $this->getJson('/api/v1/users')
            ->assertSuccessful()
            ->assertJsonStructure(['data']);
    });

    it('student cannot list users', function (): void {
        $student = User::factory()->student()->create();
        Passport::actingAs($student);

        $this->getJson('/api/v1/users')->assertForbidden();
    });

    it('admin can update any user', function (): void {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->student()->create();
        Passport::actingAs($admin);

        $this->putJson("/api/v1/users/{$user->id}", [
            'name' => 'Updated Name',
            'role' => 'teacher',
        ])->assertSuccessful()
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.role', 'teacher');
    });

    it('student cannot update another user', function (): void {
        $student = User::factory()->student()->create();
        $other = User::factory()->student()->create();
        Passport::actingAs($student);

        $this->putJson("/api/v1/users/{$other->id}", ['name' => 'Hacked'])
            ->assertForbidden();
    });

    it('admin can change user status', function (): void {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->student()->create(['status' => 'ACTIVE']);
        Passport::actingAs($admin);

        $this->patchJson("/api/v1/users/{$user->id}/status", ['status' => 'SUSPENDED'])
            ->assertSuccessful()
            ->assertJsonPath('data.status', 'SUSPENDED');
    });

    it('non-admin cannot change user status', function (): void {
        $teacher = User::factory()->teacher()->create();
        $user = User::factory()->student()->create();
        Passport::actingAs($teacher);

        $this->patchJson("/api/v1/users/{$user->id}/status", ['status' => 'SUSPENDED'])
            ->assertForbidden();
    });

    it('admin can delete a user', function (): void {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->student()->create();
        Passport::actingAs($admin);

        $this->deleteJson("/api/v1/users/{$user->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    });

    it('admin cannot delete themselves', function (): void {
        $admin = User::factory()->admin()->create();
        Passport::actingAs($admin);

        $this->deleteJson("/api/v1/users/{$admin->id}")
            ->assertForbidden();
    });

    it('user can update their own profile', function (): void {
        $user = User::factory()->student()->create();
        Passport::actingAs($user);

        $this->patchJson('/api/v1/profile', ['name' => 'New Name', 'bio' => 'Hello!'])
            ->assertSuccessful()
            ->assertJsonPath('data.name', 'New Name');
    });

    it('user can search users by name', function (): void {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['name' => 'Alice Smith']);
        User::factory()->create(['name' => 'Bob Jones']);
        Passport::actingAs($admin);

        $this->getJson('/api/v1/users?search=Alice')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data');
    });
});

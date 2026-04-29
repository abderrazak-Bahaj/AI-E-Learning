<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Resource;
use App\Models\User;

final class ResourcePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Resource $resource): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isTeacher() || $user->isAdmin();
    }

    public function update(User $user, Resource $resource): bool
    {
        return $user->isAdmin() || $user->id === $resource->course->teacher_id;
    }

    public function delete(User $user, Resource $resource): bool
    {
        return $user->isAdmin() || $user->id === $resource->course->teacher_id;
    }
}

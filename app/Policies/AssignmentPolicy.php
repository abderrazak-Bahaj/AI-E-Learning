<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Assignment;
use App\Models\User;

final class AssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Assignment $assignment): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isTeacher() || $user->isAdmin();
    }

    public function update(User $user, Assignment $assignment): bool
    {
        return $user->isAdmin() || $user->id === $assignment->course->teacher_id;
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        return $user->isAdmin() || $user->id === $assignment->course->teacher_id;
    }

    public function grade(User $user, Assignment $assignment): bool
    {
        return $user->isAdmin() || $user->id === $assignment->course->teacher_id;
    }
}

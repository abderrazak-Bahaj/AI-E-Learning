<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Enrollment;
use App\Models\User;

final class EnrollmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Enrollment $enrollment): bool
    {
        return $user->isAdmin()
            || $user->id === $enrollment->student_id
            || $user->id === $enrollment->course->teacher_id;
    }

    public function create(User $user): bool
    {
        return $user->isStudent() || $user->isAdmin();
    }

    public function delete(User $user, Enrollment $enrollment): bool
    {
        return $user->isAdmin() || $user->id === $enrollment->student_id;
    }
}

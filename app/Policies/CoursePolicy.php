<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

final class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Course $course): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isTeacher() || $user->isAdmin();
    }

    public function update(User $user, Course $course): bool
    {
        return $user->isAdmin() || $user->id === $course->teacher_id;
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->isAdmin() || $user->id === $course->teacher_id;
    }
}

<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;

final class LessonPolicy
{
    public function viewAny(User $user, Course $course): bool
    {
        return true;
    }

    public function view(User $user, Lesson $lesson): bool
    {
        return true;
    }

    public function create(User $user, Course $course): bool
    {
        return $user->isAdmin() || $user->id === $course->teacher_id;
    }

    public function update(User $user, Lesson $lesson): bool
    {
        return $user->isAdmin() || $user->id === $lesson->course->teacher_id;
    }

    public function delete(User $user, Lesson $lesson): bool
    {
        return $user->isAdmin() || $user->id === $lesson->course->teacher_id;
    }
}

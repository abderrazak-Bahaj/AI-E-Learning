<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;

final class SubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Submission $submission): bool
    {
        return $user->isAdmin()
            || $user->id === $submission->student_id
            || $user->id === $submission->assignment->course->teacher_id;
    }

    public function create(User $user): bool
    {
        return $user->isStudent();
    }

    public function grade(User $user, Submission $submission): bool
    {
        return $user->isAdmin()
            || $user->id === $submission->assignment->course->teacher_id;
    }
}

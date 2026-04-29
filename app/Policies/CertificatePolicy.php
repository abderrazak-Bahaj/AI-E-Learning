<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Certificate;
use App\Models\User;

final class CertificatePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Certificate $certificate): bool
    {
        return $user->isAdmin() || $user->id === $certificate->student_id;
    }
}

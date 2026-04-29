<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToUser;
use App\Traits\HasUuid;
use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $user_id
 * @property string|null $student_id
 * @property string $enrollment_status
 * @property string|null $education_level
 * @property string|null $major
 * @property array<string>|null $interests
 * @property string|null $date_of_birth
 * @property array<string>|null $learning_preferences
 * @property float|null $gpa
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class Student extends Model
{
    use BelongsToUser;

    /** @use HasFactory<StudentFactory> */
    use HasFactory;

    use HasUuid;

    protected $fillable = [
        'user_id',
        'student_id',
        'enrollment_status',
        'education_level',
        'major',
        'interests',
        'date_of_birth',
        'learning_preferences',
        'gpa',
    ];

    public function enrollments(): HasManyThrough
    {
        return $this->hasManyThrough(Enrollment::class, User::class, 'id', 'student_id', 'user_id', 'id');
    }

    protected function casts(): array
    {
        return [
            'interests' => 'array',
            'learning_preferences' => 'array',
            'date_of_birth' => 'date',
            'gpa' => 'float',
        ];
    }
}

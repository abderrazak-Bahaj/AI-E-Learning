<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToUser;
use App\Traits\HasUuid;
use Database\Factories\TeacherFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $user_id
 * @property string|null $specialization
 * @property string|null $qualification
 * @property string|null $expertise
 * @property array<int, array<string, mixed>>|null $education
 * @property array<int, array<string, mixed>>|null $certifications
 * @property float|null $rating
 * @property int|null $years_of_experience
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class Teacher extends Model
{
    use BelongsToUser;

    /** @use HasFactory<TeacherFactory> */
    use HasFactory;

    use HasUuid;

    protected $fillable = [
        'user_id',
        'specialization',
        'qualification',
        'expertise',
        'education',
        'certifications',
        'rating',
        'years_of_experience',
    ];

    public function courses(): HasManyThrough
    {
        return $this->hasManyThrough(Course::class, User::class, 'id', 'teacher_id', 'user_id', 'id');
    }

    protected function casts(): array
    {
        return [
            'education' => 'array',
            'certifications' => 'array',
            'rating' => 'float',
        ];
    }
}

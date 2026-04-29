<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasCourseRelations;
use App\Traits\HasUuid;
use App\Traits\Publishable;
use Database\Factories\AssignmentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $title
 * @property string $description
 * @property string $type
 * @property int|null $time_limit
 * @property int $max_attempts
 * @property int $total_points
 * @property int $passing_score
 * @property string $status
 * @property string $course_id
 * @property string|null $lesson_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
final class Assignment extends Model
{
    use HasCourseRelations;

    /** @use HasFactory<AssignmentFactory> */
    use HasFactory;
    use HasUuid;
    use Publishable;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'type',
        'time_limit',
        'max_attempts',
        'total_points',
        'passing_score',
        'status',
        'course_id',
        'lesson_id',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(AssignmentQuestion::class)->orderBy('order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function starts(): HasMany
    {
        return $this->hasMany(AssignmentStart::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeTimed(Builder $query): Builder
    {
        return $query->whereNotNull('time_limit');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isTimed(): bool
    {
        return $this->time_limit !== null;
    }

    public function hasMultipleAttempts(): bool
    {
        return $this->max_attempts > 1;
    }

    protected function casts(): array
    {
        return [
            'time_limit' => 'integer',
            'max_attempts' => 'integer',
            'total_points' => 'integer',
            'passing_score' => 'integer',
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUuid;
use Database\Factories\LessonProgressFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int $student_id
 * @property string $lesson_id
 * @property string $course_id
 * @property string $status
 * @property int $watch_time
 * @property int $last_position
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
final class LessonProgress extends Model
{
    /** @use HasFactory<LessonProgressFactory> */
    use HasFactory;

    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'lesson_id',
        'course_id',
        'status',
        'watch_time',
        'last_position',
        'started_at',
        'completed_at',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'COMPLETED');
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'IN_PROGRESS');
    }

    public function scopeForStudent(Builder $query, int|string $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isCompleted(): bool
    {
        return $this->status === 'COMPLETED';
    }

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'watch_time' => 'integer',
            'last_position' => 'integer',
        ];
    }
}

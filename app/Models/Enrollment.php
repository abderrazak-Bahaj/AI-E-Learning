<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUuid;
use Database\Factories\EnrollmentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int $student_id
 * @property string $course_id
 * @property string $status
 * @property float $progress
 * @property Carbon $enrolled_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
final class Enrollment extends Model
{
    /** @use HasFactory<EnrollmentFactory> */
    use HasFactory;

    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'course_id',
        'status',
        'progress',
        'enrolled_at',
        'completed_at',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'ACTIVE');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'COMPLETED');
    }

    public function scopeForStudent(Builder $query, int|string $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForCourse(Builder $query, string $courseId): Builder
    {
        return $query->where('course_id', $courseId);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isCompleted(): bool
    {
        return $this->status === 'COMPLETED';
    }

    public function isActive(): bool
    {
        return $this->status === 'ACTIVE';
    }

    /**
     * Get progress as a percentage (0-100).
     */
    public function getProgressPercentageAttribute(): float
    {
        return round($this->progress, 2);
    }

    /**
     * Get the last lesson in progress or the first not started lesson.
     */
    public function getLastLessonInProgress(): ?LessonProgress
    {
        return LessonProgress::query()
            ->where('lesson_progress.student_id', $this->student_id)
            ->where('lesson_progress.course_id', $this->course_id)
            ->whereIn('lesson_progress.status', ['IN_PROGRESS', 'NOT_STARTED'])
            ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
            ->orderByRaw("CASE WHEN lesson_progress.status = 'IN_PROGRESS' THEN 0 ELSE 1 END")
            ->orderBy('lesson_progress.updated_at', 'desc')
            ->orderBy('lessons.order', 'asc')
            ->select('lesson_progress.*')
            ->with('lesson')
            ->first();
    }

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'completed_at' => 'datetime',
            'progress' => 'float',
        ];
    }
}

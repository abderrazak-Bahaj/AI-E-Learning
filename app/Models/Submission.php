<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUuid;
use Database\Factories\SubmissionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int $student_id
 * @property string $assignment_id
 * @property int $attempt_number
 * @property float|null $score
 * @property bool $is_passed
 * @property string|null $feedback
 * @property string $status
 * @property Carbon|null $submitted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
final class Submission extends Model
{
    /** @use HasFactory<SubmissionFactory> */
    use HasFactory;

    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'assignment_id',
        'attempt_number',
        'score',
        'is_passed',
        'feedback',
        'status',
        'submitted_at',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SubmissionAnswer::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeGraded(Builder $query): Builder
    {
        return $query->where('status', 'GRADED');
    }

    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->where('status', 'SUBMITTED');
    }

    public function scopePassed(Builder $query): Builder
    {
        return $query->where('is_passed', true);
    }

    public function scopeForStudent(Builder $query, int|string $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isGraded(): bool
    {
        return $this->status === 'GRADED';
    }

    protected function casts(): array
    {
        return [
            'score' => 'float',
            'is_passed' => 'boolean',
            'attempt_number' => 'integer',
            'submitted_at' => 'datetime',
        ];
    }
}

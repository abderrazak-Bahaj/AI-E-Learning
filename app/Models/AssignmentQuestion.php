<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUuid;
use Database\Factories\AssignmentQuestionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $question_text
 * @property string $question_type
 * @property int $points
 * @property int $order
 * @property string|null $explanation
 * @property string $assignment_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
final class AssignmentQuestion extends Model
{
    /** @use HasFactory<AssignmentQuestionFactory> */
    use HasFactory;

    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'question_text',
        'question_type',
        'points',
        'order',
        'explanation',
        'assignment_id',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(AssignmentOption::class, 'question_id')->orderBy('order');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SubmissionAnswer::class, 'question_id');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('question_type', $type);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isChoiceBased(): bool
    {
        return in_array($this->question_type, ['MULTIPLE_CHOICE', 'TRUE_FALSE'], true);
    }

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'order' => 'integer',
        ];
    }
}

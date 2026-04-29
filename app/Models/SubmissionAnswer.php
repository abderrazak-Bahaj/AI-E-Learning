<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $submission_id
 * @property string $question_id
 * @property string|null $selected_option_id
 * @property string|null $answer
 * @property bool|null $is_correct
 * @property float|null $score
 * @property string|null $feedback
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
final class SubmissionAnswer extends Model
{
    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'submission_id',
        'question_id',
        'selected_option_id',
        'answer',
        'is_correct',
        'score',
        'feedback',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(AssignmentQuestion::class, 'question_id');
    }

    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(AssignmentOption::class, 'selected_option_id');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeCorrect(Builder $query): Builder
    {
        return $query->where('is_correct', true);
    }

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'score' => 'float',
        ];
    }
}

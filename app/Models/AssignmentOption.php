<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUuid;
use Database\Factories\AssignmentOptionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $option_text
 * @property bool $is_correct
 * @property int $order
 * @property string $question_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
final class AssignmentOption extends Model
{
    /** @use HasFactory<AssignmentOptionFactory> */
    use HasFactory;

    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'option_text',
        'is_correct',
        'order',
        'question_id',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function question(): BelongsTo
    {
        return $this->belongsTo(AssignmentQuestion::class, 'question_id');
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
            'order' => 'integer',
        ];
    }
}

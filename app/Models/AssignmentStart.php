<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $assignment_id
 * @property int $user_id
 * @property Carbon $started_at
 * @property Carbon|null $expires_at
 * @property bool $is_expired
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class AssignmentStart extends Model
{
    use HasUuid;

    protected $fillable = [
        'assignment_id',
        'user_id',
        'started_at',
        'expires_at',
        'is_expired',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_expired', false);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('is_expired', true);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function hasExpired(): bool
    {
        if ($this->is_expired) {
            return true;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return true;
        }

        return false;
    }

    public function remainingSeconds(): int
    {
        if (! $this->expires_at) {
            return PHP_INT_MAX;
        }

        return max(0, (int) now()->diffInSeconds($this->expires_at, false));
    }

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_expired' => 'boolean',
        ];
    }
}

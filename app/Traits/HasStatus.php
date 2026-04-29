<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Provides status-based scopes and helpers for models with a `status` column.
 */
trait HasStatus
{
    public function isActive(): bool
    {
        return $this->status === 'ACTIVE';
    }

    public function activate(): bool
    {
        return $this->update(['status' => 'ACTIVE']);
    }

    public function deactivate(): bool
    {
        return $this->update(['status' => 'INACTIVE']);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'ACTIVE');
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', 'INACTIVE');
    }

    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }
}

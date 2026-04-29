<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * For models with DRAFT / PUBLISHED status (Course, Lesson, Assignment).
 */
trait Publishable
{
    public function isPublished(): bool
    {
        return $this->status === 'PUBLISHED';
    }

    public function isDraft(): bool
    {
        return $this->status === 'DRAFT';
    }

    public function publish(): bool
    {
        return $this->update(['status' => 'PUBLISHED']);
    }

    public function unpublish(): bool
    {
        return $this->update(['status' => 'DRAFT']);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'PUBLISHED');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'DRAFT');
    }
}

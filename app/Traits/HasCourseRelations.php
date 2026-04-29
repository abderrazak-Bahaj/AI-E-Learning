<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Course;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * For models that belong to a Course (Lesson, Assignment, Resource, etc.).
 */
trait HasCourseRelations
{
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function scopeForCourse(Builder $query, string $courseId): Builder
    {
        return $query->where('course_id', $courseId);
    }
}

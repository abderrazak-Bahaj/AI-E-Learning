<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasCourseRelations;
use App\Traits\HasUuid;
use Database\Factories\ResourceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $title
 * @property string $file_url
 * @property int $order
 * @property string $type
 * @property bool $is_preview
 * @property string $course_id
 * @property string|null $lesson_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
final class Resource extends Model
{
    use HasCourseRelations;

    /** @use HasFactory<ResourceFactory> */
    use HasFactory;

    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'file_url',
        'order',
        'type',
        'is_preview',
        'course_id',
        'lesson_id',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopePreviewable(Builder $query): Builder
    {
        return $query->where('is_preview', true);
    }

    public function scopeCourseLevelOnly(Builder $query): Builder
    {
        return $query->whereNull('lesson_id');
    }

    protected function casts(): array
    {
        return [
            'is_preview' => 'boolean',
            'order' => 'integer',
        ];
    }
}

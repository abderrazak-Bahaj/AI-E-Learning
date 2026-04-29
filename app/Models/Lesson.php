<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasCourseRelations;
use App\Traits\HasUuid;
use App\Traits\Publishable;
use Database\Factories\LessonFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $title
 * @property string $content
 * @property string|null $video_url
 * @property int $order
 * @property int $section
 * @property int $duration
 * @property bool $is_free_preview
 * @property string $status
 * @property string $course_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
final class Lesson extends Model
{
    use HasCourseRelations;

    /** @use HasFactory<LessonFactory> */
    use HasFactory;
    use HasUuid;
    use Publishable;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'video_url',
        'order',
        'section',
        'duration',
        'is_free_preview',
        'status',
        'course_id',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class)->orderBy('order');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('section')->orderBy('order');
    }

    public function scopeFreePreview(Builder $query): Builder
    {
        return $query->where('is_free_preview', true);
    }

    public function scopeInSection(Builder $query, int $section): Builder
    {
        return $query->where('section', $section);
    }

    protected function casts(): array
    {
        return [
            'is_free_preview' => 'boolean',
            'duration' => 'integer',
            'order' => 'integer',
            'section' => 'integer',
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasSlug;
use App\Traits\HasStatus;
use App\Traits\HasUuid;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $parent_id
 * @property string|null $icon
 * @property int $order
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
final class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    use HasSlug;
    use HasStatus;
    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'icon',
        'order',
        'status',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * Resolve route model binding by slug instead of id.
     * Allows: GET /categories/web-development
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

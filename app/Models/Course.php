<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\Publishable;
use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $title
 * @property string $description
 * @property string|null $image_url
 * @property float $price
 * @property string $status
 * @property string $level
 * @property array<string>|null $skills
 * @property string $language
 * @property int $duration
 * @property string $category_id
 * @property int $teacher_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
final class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory;

    use HasUuid;
    use Publishable;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'image_url',
        'price',
        'status',
        'level',
        'skills',
        'language',
        'duration',
        'category_id',
        'teacher_id',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class)->orderBy('order');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class, 'invoice_course')
            ->withPivot('price')
            ->withTimestamps();
    }

    public function courseTeachers(): HasMany
    {
        return $this->hasMany(CourseTeacher::class);
    }

    public function additionalTeachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_teachers', 'course_id', 'teacher_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeFree(Builder $query): Builder
    {
        return $query->where('price', 0);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('price', '>', 0);
    }

    public function scopeByLevel(Builder $query, string $level): Builder
    {
        return $query->where('level', $level);
    }

    public function scopeByTeacher(Builder $query, int|string $teacherId): Builder
    {
        return $query->where('teacher_id', $teacherId);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isFree(): bool
    {
        return $this->price === 0;
    }

    public function enrolledCount(): int
    {
        return $this->enrollments()->where('status', 'ACTIVE')->count();
    }

    protected function casts(): array
    {
        return [
            'skills' => 'array',
            'price' => 'float',
            'duration' => 'integer',
        ];
    }
}

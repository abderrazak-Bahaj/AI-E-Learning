<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * Auto-generates a unique slug from the model's `name` column on creation.
 * Override $slugSource to use a different source column.
 */
trait HasSlug
{
    protected string $slugSource = 'name';

    public static function bootHasSlug(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->slug)) {
                $model->slug = static::generateUniqueSlug(
                    (string) $model->{$model->slugSource}
                );
            }
        });
    }

    public static function generateUniqueSlug(string $value): string
    {
        $slug = Str::slug($value);
        $original = $slug;
        $count = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$original}-{$count}";
            $count++;
        }

        return $slug;
    }
}

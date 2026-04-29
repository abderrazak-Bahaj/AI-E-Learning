<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * Automatically assigns a UUID as the primary key on model creation.
 * Use on any model with a UUID primary key.
 */
trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function initializeHasUuid(): void
    {
        $this->keyType = 'string';
        $this->incrementing = false;
    }
}

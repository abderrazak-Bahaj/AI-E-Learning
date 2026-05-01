<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * Extends Spatie's Permission model to use UUID primary keys.
 */
final class Permission extends SpatiePermission
{
    use HasUuids;
}

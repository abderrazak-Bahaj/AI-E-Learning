<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Extends Spatie's Role model to use UUID primary keys,
 * consistent with the rest of the application.
 */
final class Role extends SpatieRole
{
    use HasUuids;
}

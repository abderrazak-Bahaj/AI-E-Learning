<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToUser;
use App\Traits\HasUuid;
use Database\Factories\AdminFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $user_id
 * @property string|null $department
 * @property string|null $position
 * @property array<string>|null $permissions
 * @property bool $super_admin
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class Admin extends Model
{
    use BelongsToUser;

    /** @use HasFactory<AdminFactory> */
    use HasFactory;

    use HasUuid;

    protected $fillable = [
        'user_id',
        'department',
        'position',
        'permissions',
        'super_admin',
    ];

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? [], true);
    }

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'super_admin' => 'boolean',
        ];
    }
}

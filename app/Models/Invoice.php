<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUuid;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $invoice_number
 * @property int $user_id
 * @property float $subtotal
 * @property float $tax
 * @property float $discount
 * @property float $total
 * @property string $currency
 * @property string $status
 * @property string|null $payment_method
 * @property string|null $transaction_id
 * @property string|null $notes
 * @property Carbon|null $paid_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
final class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'user_id',
        'subtotal',
        'tax',
        'discount',
        'total',
        'currency',
        'status',
        'payment_method',
        'transaction_id',
        'notes',
        'paid_at',
    ];

    public static function generateNumber(): string
    {
        return 'INV-'.mb_strtoupper(Str::random(8));
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'invoice_course')
            ->withPivot('price')
            ->withTimestamps();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'PAID');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeForUser(Builder $query, int|string $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isPaid(): bool
    {
        return $this->status === 'PAID';
    }

    protected function casts(): array
    {
        return [
            'subtotal' => 'float',
            'tax' => 'float',
            'discount' => 'float',
            'total' => 'float',
            'paid_at' => 'datetime',
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUuid;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int $user_id
 * @property string|null $invoice_id
 * @property string $course_id
 * @property float $amount
 * @property string $payment_method
 * @property string|null $transaction_id
 * @property string|null $payment_gateway
 * @property array<string, mixed>|null $payment_details
 * @property string $status
 * @property string|null $error_message
 * @property Carbon|null $paid_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
final class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'invoice_id',
        'course_id',
        'amount',
        'payment_method',
        'transaction_id',
        'payment_gateway',
        'payment_details',
        'status',
        'error_message',
        'paid_at',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'COMPLETED');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'FAILED');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isCompleted(): bool
    {
        return $this->status === 'COMPLETED';
    }

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'payment_details' => 'array',
            'paid_at' => 'datetime',
        ];
    }
}

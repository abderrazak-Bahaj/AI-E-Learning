<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Invoice>
 */
final class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 29.99, 299.99);
        $tax = round($subtotal * 0.1, 2);
        $total = round($subtotal + $tax, 2);

        return [
            'invoice_number' => 'INV-'.mb_strtoupper(Str::random(8)),
            'user_id' => User::factory()->student(),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => 0,
            'total' => $total,
            'currency' => 'USD',
            'status' => 'PENDING',
            'payment_method' => null,
            'transaction_id' => null,
            'notes' => null,
            'paid_at' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'PAID',
            'payment_method' => fake()->randomElement(['paypal', 'stripe', 'credit_card']),
            'transaction_id' => mb_strtoupper(Str::random(16)),
            'paid_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'FAILED',
            'notes' => 'Payment failed due to insufficient funds.',
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'REFUNDED',
            'notes' => 'Refund processed.',
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => $user->id,
        ]);
    }
}

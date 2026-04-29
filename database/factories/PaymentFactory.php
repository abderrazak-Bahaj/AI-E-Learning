<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Course;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
final class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->student(),
            'invoice_id' => null,
            'course_id' => Course::factory(),
            'amount' => fake()->randomFloat(2, 29.99, 299.99),
            'payment_method' => 'paypal',
            'transaction_id' => 'PAYID-'.mb_strtoupper(Str::random(16)),
            'payment_gateway' => 'paypal',
            'payment_details' => $this->paypalDetails(),
            'status' => 'COMPLETED',
            'error_message' => null,
            'paid_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'PENDING',
            'paid_at' => null,
            'transaction_id' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'FAILED',
            'paid_at' => null,
            'error_message' => fake()->randomElement([
                'Insufficient funds',
                'Payment method declined',
                'Transaction timeout',
            ]),
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => $user->id,
        ]);
    }

    public function forInvoice(Invoice $invoice): static
    {
        return $this->state(fn (array $attributes): array => [
            'invoice_id' => $invoice->id,
            'user_id' => $invoice->user_id,
            'amount' => $invoice->total,
        ]);
    }

    public function forCourse(Course $course): static
    {
        return $this->state(fn (array $attributes): array => [
            'course_id' => $course->id,
            'amount' => $course->price,
        ]);
    }

    /** @return array<string, mixed> */
    private function paypalDetails(): array
    {
        return [
            'id' => mb_strtoupper(Str::random(17)),
            'intent' => 'CAPTURE',
            'status' => 'COMPLETED',
            'payer' => [
                'name' => ['given_name' => fake()->firstName(), 'surname' => fake()->lastName()],
                'email_address' => fake()->safeEmail(),
                'payer_id' => mb_strtoupper(Str::random(13)),
            ],
            'create_time' => now()->toIso8601String(),
            'update_time' => now()->toIso8601String(),
        ];
    }
}

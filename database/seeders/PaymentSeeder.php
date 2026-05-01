<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $invoiceCount = 0;
        $paymentCount = 0;

        // Create invoices for students who are enrolled in paid courses
        $students = User::role('student')->get();

        foreach ($students as $student) {
            $paidEnrollments = Enrollment::where('student_id', $student->id)
                ->whereHas('course', fn ($q) => $q->where('price', '>', 0))
                ->with('course')
                ->get();

            if ($paidEnrollments->isEmpty()) {
                continue;
            }

            // Group enrollments into 1–2 invoices
            $chunks = $paidEnrollments->chunk(fake()->numberBetween(1, 2));

            foreach ($chunks as $chunk) {
                $courses = $chunk->pluck('course');
                $subtotal = $courses->sum('price');
                $tax = round($subtotal * 0.1, 2);
                $total = round($subtotal + $tax, 2);
                $isPaid = fake()->boolean(80);

                $invoice = Invoice::create([
                    'invoice_number' => 'INV-'.mb_strtoupper(Str::random(8)),
                    'user_id' => $student->id,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'discount' => 0,
                    'total' => $total,
                    'currency' => 'USD',
                    'status' => $isPaid ? 'PAID' : fake()->randomElement(['PENDING', 'FAILED']),
                    'payment_method' => $isPaid ? 'paypal' : null,
                    'transaction_id' => $isPaid ? mb_strtoupper(Str::random(16)) : null,
                    'notes' => null,
                    'paid_at' => $isPaid ? fake()->dateTimeBetween('-30 days', 'now') : null,
                ]);

                // Attach courses to invoice pivot
                foreach ($courses as $course) {
                    $invoice->courses()->syncWithoutDetaching([
                        $course->id => ['price' => $course->price],
                    ]);
                }

                $invoiceCount++;

                // Create payment record for paid invoices
                if ($isPaid) {
                    $firstCourse = $courses->first();

                    Payment::create([
                        'user_id' => $student->id,
                        'invoice_id' => $invoice->id,
                        'course_id' => $firstCourse->id,
                        'amount' => $invoice->total,
                        'payment_method' => 'paypal',
                        'transaction_id' => 'PAYID-'.mb_strtoupper(Str::random(16)),
                        'payment_gateway' => 'paypal',
                        'payment_details' => $this->buildPaypalDetails($student, $invoice->total),
                        'status' => 'COMPLETED',
                        'error_message' => null,
                        'paid_at' => $invoice->paid_at,
                    ]);

                    $paymentCount++;
                }
            }
        }

        $this->command->info("Seeded: {$invoiceCount} invoices, {$paymentCount} payments");
    }

    /** @return array<string, mixed> */
    private function buildPaypalDetails(User $user, float $amount): array
    {
        return [
            'id' => mb_strtoupper(Str::random(17)),
            'intent' => 'CAPTURE',
            'status' => 'COMPLETED',
            'payer' => [
                'name' => ['given_name' => explode(' ', $user->name)[0], 'surname' => explode(' ', $user->name)[1] ?? ''],
                'email_address' => $user->email,
                'payer_id' => mb_strtoupper(Str::random(13)),
            ],
            'amount' => ['currency_code' => 'USD', 'value' => $amount],
            'create_time' => now()->toIso8601String(),
            'update_time' => now()->toIso8601String(),
        ];
    }
}

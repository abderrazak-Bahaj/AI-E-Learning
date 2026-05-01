<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\PaypalServiceInterface;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\CaptureOrderRequest;
use App\Http\Requests\Api\V1\CreateOrderRequest;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\PaymentResource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\LessonProgress;
use App\Models\Payment;
use App\Notifications\EnrollmentConfirmed;
use App\Notifications\PaymentSuccessful;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

final class PaymentController extends ApiController
{
    public function __construct(private readonly PaypalServiceInterface $paypal) {}

    // ── List user payments ─────────────────────────────────────────────────────

    /**
     * List the authenticated user's payment history.
     */
    #[\Dedoc\Scramble\Attributes\QueryParameter('per_page', description: 'Items per page (max 100).', type: 'integer', default: 15)]
    #[\Dedoc\Scramble\Attributes\QueryParameter('page', description: 'Page number.', type: 'integer', default: 1)]
    public function index(Request $request): JsonResponse
    {
        $payments = Payment::query()
            ->where('user_id', $request->user()->id)
            ->with('course', 'invoice')
            ->latest()
            ->paginate(15);

        return $this->success(PaymentResource::collection($payments));
    }

    // ── Step 1: Create a PayPal order ──────────────────────────────────────────

    /**
     * Validates the cart, builds an invoice, and returns a PayPal approval URL.
     * Free courses are enrolled immediately without going through PayPal.
     */
    /**
     * Step 1: Create a PayPal order for course purchase.
     *
     * Free courses are enrolled immediately without PayPal.
     * Paid courses return an `approval_url` — redirect the user there to approve payment.
     * After approval, call POST /payments/capture-order.
     */
    public function createOrder(CreateOrderRequest $request): JsonResponse
    {
        $courses = Course::query()
            ->whereIn('id', $request->course_ids)
            ->where('status', 'PUBLISHED')
            ->get();

        if ($courses->count() !== count($request->course_ids)) {
            return $this->error('One or more courses are unavailable.', 422);
        }

        // Guard: already enrolled
        $alreadyEnrolled = Enrollment::query()
            ->where('student_id', $request->user()->id)
            ->whereIn('course_id', $request->course_ids)
            ->pluck('course_id');

        if ($alreadyEnrolled->isNotEmpty()) {
            return $this->error('You are already enrolled in one or more of these courses.', 409);
        }

        // Free courses → enroll immediately, no payment needed
        $freeCourses = $courses->where('price', 0);
        $paidCourses = $courses->where('price', '>', 0);

        try {
            DB::beginTransaction();

            foreach ($freeCourses as $course) {
                $this->enroll($request->user()->id, $course);
            }

            if ($paidCourses->isEmpty()) {
                DB::commit();

                return $this->success(
                    ['enrolled_course_ids' => $freeCourses->pluck('id')],
                    'Enrolled in free courses successfully.'
                );
            }

            // Build invoice for paid courses
            $invoice = $this->buildInvoice($request->user()->id, $paidCourses);

            // Create PayPal order
            $order = $this->paypal->createOrder(
                purchaseUnits: [[
                    'reference_id' => $invoice->invoice_number,
                    'description' => "CoursePalette — {$paidCourses->count()} course(s)",
                    'amount' => [
                        'currency_code' => $invoice->currency,
                        'value' => number_format($invoice->total, 2, '.', ''),
                    ],
                ]],
                returnUrl: config('app.frontend_url').'/checkout/success?invoice_id='.$invoice->id,
                cancelUrl: config('app.frontend_url').'/checkout/cancel?invoice_id='.$invoice->id,
            );

            DB::commit();

            return $this->success([
                'order_id' => $order['id'],
                'invoice' => new InvoiceResource($invoice->load('courses')),
                'approval_url' => $this->paypal->approvalUrl($order),
            ], 'PayPal order created. Redirect the user to the approval URL.');

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('PaymentController@createOrder failed', [
                'user_id' => $request->user()->id,
                'course_ids' => $request->course_ids,
                'error' => $e->getMessage(),
            ]);

            return $this->error('Failed to create payment order. Please try again.', 500);
        }
    }

    // ── Step 2: Capture the approved PayPal order ──────────────────────────────

    /**
     * Called after the user approves the payment on PayPal.
     * Captures the order, records the transaction, and creates enrollments.
     */
    /**
     * Step 2: Capture an approved PayPal order.
     *
     * Call this after the user approves payment on PayPal.
     * Creates Payment record, marks Invoice as PAID, and enrolls the student.
     * Idempotent — safe to call multiple times on the same invoice.
     */
    public function captureOrder(CaptureOrderRequest $request): JsonResponse
    {
        $invoice = Invoice::query()
            ->with('courses')
            ->findOrFail($request->invoice_id);

        // Idempotency: already paid
        if ($invoice->isPaid()) {
            return $this->success(
                new InvoiceResource($invoice),
                'This invoice has already been paid.'
            );
        }

        if ($invoice->user_id !== $request->user()->id) {
            return $this->forbidden('This invoice does not belong to you.');
        }

        try {
            DB::beginTransaction();

            // Check current order status before attempting capture
            $orderDetails = $this->paypal->getOrder($request->order_id);

            $captureResult = match ($orderDetails['status']) {
                'COMPLETED' => $orderDetails,
                'APPROVED' => $this->paypal->captureOrder($request->order_id),
                default => throw new RuntimeException(
                    "PayPal order status is '{$orderDetails['status']}', cannot capture."
                ),
            };

            if (($captureResult['status'] ?? '') !== 'COMPLETED') {
                throw new RuntimeException('PayPal capture did not complete.');
            }

            // Record payment
            $payment = $this->recordPayment($invoice, $captureResult);

            // Mark invoice paid
            $invoice->update([
                'status' => 'PAID',
                'payment_method' => 'paypal',
                'transaction_id' => $captureResult['id'],
                'paid_at' => now(),
            ]);

            // Enroll student in all courses on the invoice
            foreach ($invoice->courses as $course) {
                $this->enroll($invoice->user_id, $course);
            }

            DB::commit();

            // Notify after commit (queued — won't block the response)
            $invoice->user->notify(new PaymentSuccessful($invoice->fresh('courses')));

            return $this->success([
                'payment' => new PaymentResource($payment->load('course', 'invoice')),
                'invoice' => new InvoiceResource($invoice->fresh('courses')),
            ], 'Payment captured and enrollment confirmed.');

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('PaymentController@captureOrder failed', [
                'invoice_id' => $invoice->id,
                'order_id' => $request->order_id,
                'error' => $e->getMessage(),
            ]);

            // Mark invoice as failed so the user knows
            $invoice->update(['status' => 'FAILED', 'notes' => $e->getMessage()]);

            return $this->error('Payment capture failed: '.$e->getMessage(), 500);
        }
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    /**
     * Build and persist an invoice for the given paid courses.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, Course>  $courses
     */
    private function buildInvoice(string $userId, \Illuminate\Database\Eloquent\Collection $courses): Invoice
    {
        $subtotal = $courses->sum('price');
        $tax = round($subtotal * 0.10, 2);
        $total = round($subtotal + $tax, 2);

        $invoice = Invoice::query()->create([
            'invoice_number' => Invoice::generateNumber(),
            'user_id' => $userId,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => 0,
            'total' => $total,
            'currency' => 'USD',
            'status' => 'PENDING',
        ]);

        // Attach courses to the invoice pivot
        $invoice->courses()->attach(
            $courses->mapWithKeys(fn (Course $c) => [$c->id => ['price' => $c->price]])
        );

        return $invoice;
    }

    /**
     * Persist a Payment record from a completed PayPal capture.
     *
     * @param  array<string, mixed>  $captureResult
     */
    private function recordPayment(Invoice $invoice, array $captureResult): Payment
    {
        $firstCourse = $invoice->courses->first();

        return Payment::query()->create([
            'user_id' => $invoice->user_id,
            'invoice_id' => $invoice->id,
            'course_id' => $firstCourse->id,
            'amount' => $invoice->total,
            'payment_method' => 'paypal',
            'transaction_id' => $captureResult['id'],
            'payment_gateway' => 'paypal',
            'payment_details' => $captureResult,
            'status' => 'COMPLETED',
            'paid_at' => now(),
        ]);
    }

    /**
     * Enroll a student in a course and initialise lesson progress records.
     */
    private function enroll(string $userId, Course $course): void
    {
        // Idempotent — skip if already enrolled
        $enrollment = Enrollment::query()->firstOrCreate(
            ['student_id' => $userId, 'course_id' => $course->id],
            ['status' => 'ACTIVE', 'progress' => 0, 'enrolled_at' => now()]
        );

        if (! $enrollment->wasRecentlyCreated) {
            return;
        }

        // Notify the student of their new enrollment (queued)
        $enrollment->student->notify(new EnrollmentConfirmed($enrollment->load('course')));

        // Initialise NOT_STARTED progress for every lesson
        $course->loadMissing('lessons');

        $progressRows = $course->lessons->map(fn ($lesson) => [
            'student_id' => $userId,
            'lesson_id' => $lesson->id,
            'course_id' => $course->id,
            'status' => 'NOT_STARTED',
            'watch_time' => 0,
            'last_position' => 0,
        ])->all();

        LessonProgress::query()->insertOrIgnore($progressRows);
    }
}

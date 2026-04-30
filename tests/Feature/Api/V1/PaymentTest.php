<?php

declare(strict_types=1);

use App\Contracts\PaypalServiceInterface;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\User;
use App\Notifications\PaymentSuccessful;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Passport\Passport;

uses(LazilyRefreshDatabase::class);

describe('Payment — Create Order', function (): void {
    it('creates a PayPal order for paid courses', function (): void {
        $student = User::factory()->student()->create();
        $course = Course::factory()->published()->create(['price' => 49.99]);
        Passport::actingAs($student);

        $this->mock(PaypalServiceInterface::class, function ($mock): void {
            $mock->shouldReceive('createOrder')->once()->andReturn([
                'id' => 'PAYPAL-ORDER-123',
                'links' => [['rel' => 'approve', 'href' => 'https://paypal.com/approve']],
            ]);
            $mock->shouldReceive('approvalUrl')->once()->andReturn('https://paypal.com/approve');
        });

        $this->postJson('/api/v1/payments/create-order', ['course_ids' => [$course->id]])
            ->assertSuccessful()
            ->assertJsonPath('data.order_id', 'PAYPAL-ORDER-123')
            ->assertJsonStructure(['data' => ['order_id', 'invoice', 'approval_url']]);

        $this->assertDatabaseHas('invoices', ['user_id' => $student->id, 'status' => 'PENDING']);
    });

    it('enrolls immediately in free courses without PayPal', function (): void {
        Notification::fake();

        $student = User::factory()->student()->create();
        $course = Course::factory()->free()->published()->create();
        Passport::actingAs($student);

        $this->postJson('/api/v1/payments/create-order', ['course_ids' => [$course->id]])
            ->assertSuccessful()
            ->assertJsonPath('data.enrolled_course_ids.0', $course->id);

        $this->assertDatabaseHas('enrollments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);
    });

    it('rejects already enrolled courses', function (): void {
        $student = User::factory()->student()->create();
        $course = Course::factory()->published()->create(['price' => 49.99]);
        Enrollment::factory()->create(['student_id' => $student->id, 'course_id' => $course->id]);
        Passport::actingAs($student);

        $this->postJson('/api/v1/payments/create-order', ['course_ids' => [$course->id]])
            ->assertStatus(409);
    });
});

describe('Payment — Capture Order', function (): void {
    it('captures order, creates payment, and enrolls student', function (): void {
        Notification::fake();

        $student = User::factory()->student()->create();
        $course = Course::factory()->published()->create(['price' => 49.99]);
        $invoice = Invoice::factory()->forUser($student)->create([
            'status' => 'PENDING',
            'total' => 54.99,
        ]);
        $invoice->courses()->attach($course->id, ['price' => $course->price]);

        Passport::actingAs($student);

        $captureResult = [
            'id' => 'CAPTURE-123',
            'status' => 'COMPLETED',
        ];

        $this->mock(PaypalServiceInterface::class, function ($mock) use ($captureResult): void {
            $mock->shouldReceive('getOrder')->once()->andReturn(['status' => 'APPROVED']);
            $mock->shouldReceive('captureOrder')->once()->andReturn($captureResult);
        });

        $this->postJson('/api/v1/payments/capture-order', [
            'order_id' => 'PAYPAL-ORDER-123',
            'invoice_id' => $invoice->id,
        ])->assertSuccessful()
            ->assertJsonStructure(['data' => ['payment', 'invoice']]);

        $this->assertDatabaseHas('payments', ['user_id' => $student->id, 'status' => 'COMPLETED']);
        $this->assertDatabaseHas('enrollments', ['student_id' => $student->id, 'course_id' => $course->id]);
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'PAID']);

        Notification::assertSentTo($student, PaymentSuccessful::class);
    });

    it('is idempotent on already-paid invoice', function (): void {
        $student = User::factory()->student()->create();
        $invoice = Invoice::factory()->forUser($student)->paid()->create();
        Passport::actingAs($student);

        $this->postJson('/api/v1/payments/capture-order', [
            'order_id' => 'PAYPAL-ORDER-123',
            'invoice_id' => $invoice->id,
        ])->assertSuccessful()
            ->assertJsonPath('message', 'This invoice has already been paid.');
    });

    it('rejects capture for another user invoice', function (): void {
        $student = User::factory()->student()->create();
        $other = User::factory()->student()->create();
        $invoice = Invoice::factory()->forUser($other)->create(['status' => 'PENDING']);
        Passport::actingAs($student);

        $this->postJson('/api/v1/payments/capture-order', [
            'order_id' => 'PAYPAL-ORDER-123',
            'invoice_id' => $invoice->id,
        ])->assertForbidden();
    });
});

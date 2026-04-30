<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class PaymentSuccessful extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Invoice $invoice) {}

    /** @return array<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $courses = $this->invoice->courses;

        $mail = (new MailMessage)
            ->subject("Payment confirmed — Invoice #{$this->invoice->invoice_number}")
            ->greeting("Thank you, {$notifiable->name}!")
            ->line("Your payment of **\${$this->invoice->total} {$this->invoice->currency}** has been confirmed.")
            ->line("**Invoice:** #{$this->invoice->invoice_number}")
            ->line('**Courses enrolled:**');

        foreach ($courses as $course) {
            $mail->line("- {$course->title}");
        }

        return $mail
            ->action('View Invoice', config('app.frontend_url').'/invoices/'.$this->invoice->id)
            ->line('Start learning today!')
            ->salutation('The '.config('app.name').' Team');
    }
}

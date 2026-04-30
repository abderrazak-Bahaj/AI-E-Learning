<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Certificate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class CertificateIssued extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Certificate $certificate) {}

    /** @return array<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $course = $this->certificate->course;

        return (new MailMessage)
            ->subject("Your certificate for {$course->title} is ready!")
            ->greeting("Congratulations, {$notifiable->name}!")
            ->line("You have completed **{$course->title}** and earned your certificate.")
            ->line("Certificate Number: **{$this->certificate->certificate_number}**")
            ->action('Download Certificate', config('app.url').'/api/v1/certificates/'.$this->certificate->id.'/download')
            ->line('Share your achievement with the world!')
            ->salutation('The '.config('app.name').' Team');
    }
}

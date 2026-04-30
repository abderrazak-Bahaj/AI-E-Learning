<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class EnrollmentConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Enrollment $enrollment) {}

    /** @return array<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $course = $this->enrollment->course;

        return (new MailMessage)
            ->subject("You're enrolled in {$course->title}!")
            ->greeting("Hello {$notifiable->name}!")
            ->line("You have successfully enrolled in **{$course->title}**.")
            ->line('You can start learning right away.')
            ->action('Start Learning', config('app.frontend_url').'/courses/'.$course->id)
            ->line('Happy learning!')
            ->salutation('The '.config('app.name').' Team');
    }
}

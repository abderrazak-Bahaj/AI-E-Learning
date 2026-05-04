<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class TwoFactorAuthenticationNotification extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly string $code,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Two-Factor Authentication Code')
            ->greeting("Hello {$notifiable->name}!")
            ->line('You requested a two-factor authentication code. Use the code below to complete your login:')
            ->line('')
            ->line("**{$this->code}**")
            ->line('')
            ->line('This code will expire in 10 minutes.')
            ->line('If you did not request this code, please ignore this email and ensure your account is secure.')
            ->salutation('The '.config('app.name').' Team');
    }
}

<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class AccountSecurityAlertNotification extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly string $alertType,
        public readonly string $description,
        public readonly string $actionUrl,
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
            ->subject('Security Alert: Action Required')
            ->greeting("Hello {$notifiable->name}!")
            ->line("**Alert Type:** {$this->alertType}")
            ->line("**Description:** {$this->description}")
            ->line('')
            ->line('If this was you, no action is needed. If this wasn\'t you, please secure your account immediately.')
            ->action('Review Account Security', $this->actionUrl)
            ->line('If you have any concerns about your account security, please contact our support team.')
            ->salutation('The '.config('app.name').' Team');
    }
}

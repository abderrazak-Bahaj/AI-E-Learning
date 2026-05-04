<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class AccountActivationNotification extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly string $activationUrl,
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
            ->subject('Activate Your Account')
            ->greeting("Hello {$notifiable->name}!")
            ->line('Your account has been created. Please activate it by clicking the button below.')
            ->action('Activate Account', $this->activationUrl)
            ->line('This activation link will expire in 7 days.')
            ->line('If you did not create this account, please contact our support team.')
            ->salutation('The '.config('app.name').' Team');
    }
}

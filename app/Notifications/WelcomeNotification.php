<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class WelcomeNotification extends Notification
{
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
            ->subject('Welcome to '.config('app.name').'!')
            ->greeting("Welcome, {$notifiable->name}!")
            ->line('Thank you for joining our learning community!')
            ->line('We\'re excited to have you on board. Here\'s what you can do next:')
            ->line('- Complete your profile')
            ->line('- Explore our course catalog')
            ->line('- Enroll in your first course')
            ->line('- Connect with other learners')
            ->action('Get Started', config('app.frontend_url').'/dashboard')
            ->line('If you have any questions, feel free to reach out to our support team.')
            ->salutation('The '.config('app.name').' Team');
    }
}

<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

final class VerifyEmailNotification extends VerifyEmail
{
    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        // Generate the backend verification URL
        $backendUrl = $this->verificationUrl($notifiable);

        // Extract the token and email from the backend URL
        $parsedUrl = parse_url($backendUrl);
        $path = $parsedUrl['path'];
        $query = $parsedUrl['query'];

        // Extract id and hash from the path
        preg_match('/\/email\/verify\/([^\/]+)\/([^\/]+)/', $path, $matches);
        $id = $matches[1] ?? null;
        $hash = $matches[2] ?? null;

        // Build the frontend verification URL
        $frontendUrl = config('app.frontend_url').'/verify-email?id='.$id.'&hash='.$hash.'&'.$query;

        return (new MailMessage)
            ->subject('Verify Your Email Address')
            ->greeting("Hello {$notifiable->name}!")
            ->line('Thank you for registering! Please verify your email address to complete your registration.')
            ->action('Verify Email Address', $frontendUrl)
            ->line('This verification link will expire in 24 hours.')
            ->line('If you did not create this account, no further action is required.')
            ->salutation('The '.config('app.name').' Team');
    }
}

<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class VerifyEmailChangeNotification extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly string $newEmail,
        public readonly string $verificationUrl,
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
        // Convert backend URL to frontend URL if needed
        $frontendUrl = $this->convertToFrontendUrl($this->verificationUrl);

        return (new MailMessage)
            ->subject('Verify Your New Email Address')
            ->greeting("Hello {$notifiable->name}!")
            ->line("You requested to change your email address to: **{$this->newEmail}**")
            ->line('Please verify this new email address by clicking the button below.')
            ->action('Verify New Email', $frontendUrl)
            ->line('This verification link will expire in 24 hours.')
            ->line('If you did not request this change, please ignore this email.')
            ->salutation('The '.config('app.name').' Team');
    }

    /**
     * Convert backend verification URL to frontend URL.
     */
    private function convertToFrontendUrl(string $backendUrl): string
    {
        // If it's already a frontend URL, return as is
        if (str_contains($backendUrl, config('app.frontend_url'))) {
            return $backendUrl;
        }

        // Extract the token and email from the backend URL
        $parsedUrl = parse_url($backendUrl);
        $path = $parsedUrl['path'];
        $query = $parsedUrl['query'];

        // Extract id and hash from the path
        preg_match('/\/email\/verify\/([^\/]+)\/([^\/]+)/', $path, $matches);
        $id = $matches[1] ?? null;
        $hash = $matches[2] ?? null;

        // Build the frontend verification URL
        return config('app.frontend_url').'/verify-email?id='.$id.'&hash='.$hash.'&'.$query;
    }
}

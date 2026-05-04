<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

final class ResetPasswordNotification extends ResetPassword
{
    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $frontendResetUrl = config('app.frontend_url').'/reset-password?token='.$this->token.'&email='.urlencode($notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->subject('Reset Your Password')
            ->greeting("Hello {$notifiable->name}!")
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', $frontendResetUrl)
            ->line('This password reset link will expire in '.config('auth.passwords.users.expire', 60).' minutes.')
            ->line('If you did not request a password reset, no further action is required.')
            ->salutation('The '.config('app.name').' Team');
    }
}

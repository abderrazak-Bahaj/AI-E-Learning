<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\AccountActivationNotification;
use App\Notifications\AccountSecurityAlertNotification;
use App\Notifications\TwoFactorAuthenticationNotification;
use App\Notifications\VerifyEmailChangeNotification;
use App\Notifications\VerifyEmailReminderNotification;
use App\Notifications\VerifyEmailNotification;
use App\Notifications\WelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

describe('Email Notifications', function (): void {
    beforeEach(function (): void {
        Notification::fake();
    });

    it('sends verify email notification', function (): void {
        $user = User::factory()->create();

        $user->sendEmailVerificationNotification();

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    });

    it('sends welcome notification', function (): void {
        $user = User::factory()->create();

        $user->sendWelcomeNotification();

        Notification::assertSentTo($user, WelcomeNotification::class);
    });

    it('sends verify email change notification', function (): void {
        $user = User::factory()->create();
        $verificationUrl = 'https://example.com/verify-email-change?token=abc123';
        $newEmail = 'newemail@example.com';

        $user->notify(new VerifyEmailChangeNotification(
            newEmail: $newEmail,
            verificationUrl: $verificationUrl,
        ));

        Notification::assertSentTo($user, VerifyEmailChangeNotification::class, function ($notification) use ($newEmail) {
            return $notification->newEmail === $newEmail;
        });
    });

    it('sends account activation notification', function (): void {
        $user = User::factory()->create();
        $activationUrl = 'https://example.com/activate?token=abc123';

        $user->notify(new AccountActivationNotification(
            activationUrl: $activationUrl,
        ));

        Notification::assertSentTo($user, AccountActivationNotification::class);
    });

    it('sends two factor authentication notification', function (): void {
        $user = User::factory()->create();
        $code = '123456';

        $user->notify(new TwoFactorAuthenticationNotification(
            code: $code,
        ));

        Notification::assertSentTo($user, TwoFactorAuthenticationNotification::class, function ($notification) use ($code) {
            return $notification->code === $code;
        });
    });

    it('sends verify email reminder notification', function (): void {
        $user = User::factory()->create();
        $verificationUrl = 'https://example.com/verify-email?token=abc123';

        $user->notify(new VerifyEmailReminderNotification(
            verificationUrl: $verificationUrl,
        ));

        Notification::assertSentTo($user, VerifyEmailReminderNotification::class);
    });

    it('sends account security alert notification', function (): void {
        $user = User::factory()->create();
        $actionUrl = 'https://example.com/security';
        $alertType = 'Unusual Login';

        $user->notify(new AccountSecurityAlertNotification(
            alertType: $alertType,
            description: 'A login attempt was made from a new device',
            actionUrl: $actionUrl,
        ));

        Notification::assertSentTo($user, AccountSecurityAlertNotification::class, function ($notification) use ($alertType) {
            return $notification->alertType === $alertType;
        });
    });
});

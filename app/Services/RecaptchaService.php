<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class RecaptchaService
{
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    private const MIN_SCORE = 0.5;

    /**
     * Verify a reCAPTCHA v3 token.
     * Always returns true in the testing environment.
     */
    public function verify(string $token): bool
    {
        if (App::environment('testing')) {
            return true;
        }

        $secret = config('services.recaptcha.secret');

        if (empty($secret)) {
            Log::warning('reCAPTCHA secret key is not configured — skipping verification.');

            return true;
        }

        try {
            $response = Http::timeout(5)
                ->asForm()
                ->post(self::VERIFY_URL, [
                    'secret' => $secret,
                    'response' => $token,
                ]);

            if ($response->failed()) {
                return false;
            }

            $data = $response->json();

            return ($data['success'] ?? false) === true
                && ($data['score'] ?? 0) >= self::MIN_SCORE;

        } catch (Throwable $e) {
            Log::error('reCAPTCHA verification error', ['error' => $e->getMessage()]);

            return false;
        }
    }
}

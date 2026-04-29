<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Wraps the PayPal REST API v2.
 *
 * Credentials are read from config/services.php → paypal.
 * Access tokens are cached for their TTL to avoid redundant auth calls.
 */
final class PaypalService
{
    private readonly string $baseUrl;

    private readonly string $clientId;

    private readonly string $clientSecret;

    public function __construct()
    {
        $this->baseUrl = (string) config('services.paypal.base_url');
        $this->clientId = (string) config('services.paypal.client_id');
        $this->clientSecret = (string) config('services.paypal.client_secret');
    }

    // ── Public API ─────────────────────────────────────────────────────────────

    /**
     * Create a PayPal order and return the order payload.
     *
     * @param  array<string, mixed>  $purchaseUnits
     * @return array<string, mixed>
     */
    public function createOrder(array $purchaseUnits, string $returnUrl, string $cancelUrl): array
    {
        $response = $this->client()->post('/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => $purchaseUnits,
            'application_context' => [
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
                'brand_name' => config('app.name'),
                'user_action' => 'PAY_NOW',
            ],
        ]);

        $this->assertSuccess($response, 'create order');

        return $response->json();
    }

    /**
     * Fetch the current status of a PayPal order.
     *
     * @return array<string, mixed>
     */
    public function getOrder(string $orderId): array
    {
        $response = $this->client()->get("/v2/checkout/orders/{$orderId}");

        $this->assertSuccess($response, 'get order');

        return $response->json();
    }

    /**
     * Capture a previously approved PayPal order.
     *
     * @return array<string, mixed>
     */
    public function captureOrder(string $orderId): array
    {
        $response = $this->client()
            ->post("/v2/checkout/orders/{$orderId}/capture");

        $this->assertSuccess($response, 'capture order');

        return $response->json();
    }

    /**
     * Extract the approval URL from an order payload.
     */
    public function approvalUrl(array $order): string
    {
        $link = collect($order['links'] ?? [])
            ->firstWhere('rel', 'approve');

        if (! $link) {
            throw new RuntimeException('PayPal approval URL not found in order response.');
        }

        return $link['href'];
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    /**
     * Build an authenticated HTTP client for the PayPal API.
     */
    private function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout(30)
            ->connectTimeout(10)
            ->retry(2, 500, throw: false)
            ->withToken($this->accessToken())
            ->acceptJson()
            ->asJson();
    }

    /**
     * Obtain a Bearer token, caching it until 60 s before expiry.
     */
    private function accessToken(): string
    {
        return Cache::remember('paypal_access_token', 3540, function (): string {
            $response = Http::baseUrl($this->baseUrl)
                ->timeout(15)
                ->withBasicAuth($this->clientId, $this->clientSecret)
                ->asForm()
                ->post('/v1/oauth2/token', ['grant_type' => 'client_credentials']);

            if ($response->failed()) {
                throw new RuntimeException(
                    'PayPal authentication failed: '.$response->body()
                );
            }

            return $response->json('access_token');
        });
    }

    /**
     * Throw a descriptive exception when a PayPal response is not 2xx.
     */
    private function assertSuccess(\Illuminate\Http\Client\Response $response, string $action): void
    {
        if ($response->failed()) {
            $message = $response->json('message') ?? $response->body();
            throw new RuntimeException("PayPal {$action} failed: {$message}");
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Contracts;

interface PaypalServiceInterface
{
    /** @param array<string, mixed> $purchaseUnits */
    public function createOrder(array $purchaseUnits, string $returnUrl, string $cancelUrl): array;

    public function getOrder(string $orderId): array;

    public function captureOrder(string $orderId): array;

    public function approvalUrl(array $order): string;
}

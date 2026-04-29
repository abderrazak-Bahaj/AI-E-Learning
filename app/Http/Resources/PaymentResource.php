<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Payment */
final class PaymentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'transaction_id' => $this->transaction_id,
            'payment_gateway' => $this->payment_gateway,
            'status' => $this->status,
            'error_message' => $this->error_message,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'course' => new CourseResource($this->whenLoaded('course')),
            'invoice' => new InvoiceResource($this->whenLoaded('invoice')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

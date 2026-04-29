<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string $order_id
 * @property string $invoice_id
 */
final class CaptureOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isStudent() || $this->user()->isAdmin();
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'order_id' => ['required', 'string'],
            'invoice_id' => ['required', 'uuid', 'exists:invoices,id'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property array<string> $course_ids
 */
final class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isStudent() || $this->user()->isAdmin();
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'course_ids' => ['required', 'array', 'min:1'],
            'course_ids.*' => ['required', 'uuid', 'exists:courses,id'],
        ];
    }
}

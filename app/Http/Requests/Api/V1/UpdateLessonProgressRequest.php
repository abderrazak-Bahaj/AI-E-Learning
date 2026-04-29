<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateLessonProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isStudent();
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'watch_time' => ['required', 'integer', 'min:0'],
            'last_position' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:NOT_STARTED,IN_PROGRESS,COMPLETED'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StoreSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isStudent();
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'answers' => ['required', 'array', 'min:1'],
            'answers.*.question_id' => ['required', 'uuid', 'exists:assignment_questions,id'],
            'answers.*.selected_option_id' => ['nullable', 'uuid', 'exists:assignment_options,id'],
            'answers.*.answer' => ['nullable', 'string'],
        ];
    }
}

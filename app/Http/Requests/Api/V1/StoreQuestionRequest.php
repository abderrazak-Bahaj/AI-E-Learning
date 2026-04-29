<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isTeacher() || $this->user()->isAdmin();
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'question_text' => ['required', 'string'],
            'question_type' => ['required', 'in:MULTIPLE_CHOICE,TRUE_FALSE,SHORT_ANSWER,ESSAY'],
            'points' => ['required', 'integer', 'min:1'],
            'order' => ['nullable', 'integer', 'min:1'],
            'explanation' => ['nullable', 'string'],
            'options' => ['required_if:question_type,MULTIPLE_CHOICE,TRUE_FALSE', 'nullable', 'array', 'min:2'],
            'options.*.option_text' => ['required', 'string'],
            'options.*.is_correct' => ['required', 'boolean'],
            'options.*.order' => ['nullable', 'integer', 'min:1'],
        ];
    }
}

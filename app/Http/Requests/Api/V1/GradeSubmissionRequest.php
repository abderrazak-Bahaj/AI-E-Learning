<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class GradeSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isTeacher() || $this->user()->isAdmin();
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'score' => ['required', 'numeric', 'min:0'],
            'feedback' => ['nullable', 'string', 'max:2000'],
            'answers' => ['nullable', 'array'],
            'answers.*.id' => ['required', 'uuid', 'exists:submission_answers,id'],
            'answers.*.score' => ['required', 'numeric', 'min:0'],
            'answers.*.feedback' => ['nullable', 'string'],
        ];
    }
}

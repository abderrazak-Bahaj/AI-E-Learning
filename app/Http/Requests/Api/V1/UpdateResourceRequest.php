<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isTeacher() || $this->user()->isAdmin();
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'file_url' => ['sometimes', 'url', 'max:500'],
            'type' => ['sometimes', 'in:PDF,VIDEO,AUDIO,LINK,OTHER'],
            'order' => ['nullable', 'integer', 'min:1'],
            'is_preview' => ['nullable', 'boolean'],
            'lesson_id' => ['nullable', 'uuid', 'exists:lessons,id'],
        ];
    }
}

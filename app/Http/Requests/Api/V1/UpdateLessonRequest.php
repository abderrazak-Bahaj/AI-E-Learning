<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateLessonRequest extends FormRequest
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
            'content' => ['sometimes', 'string'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'order' => ['sometimes', 'integer', 'min:1'],
            'section' => ['sometimes', 'integer', 'min:1'],
            'duration' => ['sometimes', 'integer', 'min:1'],
            'is_free_preview' => ['nullable', 'boolean'],
            'status' => ['sometimes', 'in:DRAFT,PUBLISHED'],
        ];
    }
}

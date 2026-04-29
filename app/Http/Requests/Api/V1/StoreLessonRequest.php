<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isTeacher() || $this->user()->isAdmin();
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'order' => ['required', 'integer', 'min:1'],
            'section' => ['required', 'integer', 'min:1'],
            'duration' => ['required', 'integer', 'min:1'],
            'is_free_preview' => ['nullable', 'boolean'],
            'status' => ['nullable', 'in:DRAFT,PUBLISHED'],
        ];
    }
}

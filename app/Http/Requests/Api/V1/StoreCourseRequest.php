<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StoreCourseRequest extends FormRequest
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
            'description' => ['required', 'string'],
            'image_url' => ['nullable', 'url', 'max:500'],
            'price' => ['required', 'numeric', 'min:0'],
            'level' => ['required', 'in:BEGINNER,INTERMEDIATE,ADVANCED'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'max:50'],
            'language' => ['nullable', 'string', 'max:50'],
            'duration' => ['nullable', 'integer', 'min:0'],
            'category_id' => ['required', 'uuid', 'exists:categories,id'],
            'status' => ['nullable', 'in:DRAFT,PUBLISHED,ARCHIVED'],
        ];
    }
}

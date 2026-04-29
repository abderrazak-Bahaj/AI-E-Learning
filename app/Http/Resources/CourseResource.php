<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Course */
final class CourseResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'price' => $this->price,
            'status' => $this->status,
            'level' => $this->level,
            'skills' => $this->skills,
            'language' => $this->language,
            'duration' => $this->duration,
            'is_free' => $this->isFree(),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'teacher' => new UserResource($this->whenLoaded('teacher')),
            'lessons_count' => $this->whenCounted('lessons'),
            'enrollments_count' => $this->whenCounted('enrollments'),
            'lessons' => LessonResource::collection($this->whenLoaded('lessons')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

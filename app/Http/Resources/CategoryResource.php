<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Category */
final class CategoryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'order' => $this->order,
            'status' => $this->status,
            'parent_id' => $this->parent_id,
            'parent' => new self($this->whenLoaded('parent')),
            'children' => self::collection($this->whenLoaded('children')),
            'courses_count' => $this->whenCounted('courses'),
            'courses' => CourseResource::collection($this->whenLoaded('courses')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

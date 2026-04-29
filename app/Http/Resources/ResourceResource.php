<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Resource */
final class ResourceResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'file_url' => $this->file_url,
            'type' => $this->type,
            'order' => $this->order,
            'is_preview' => $this->is_preview,
            'course_id' => $this->course_id,
            'lesson_id' => $this->lesson_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

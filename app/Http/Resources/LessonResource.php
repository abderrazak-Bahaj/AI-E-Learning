<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Lesson */
final class LessonResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'video_url' => $this->video_url,
            'order' => $this->order,
            'section' => $this->section,
            'duration' => $this->duration,
            'is_free_preview' => $this->is_free_preview,
            'status' => $this->status,
            'course_id' => $this->course_id,
            'resources' => ResourceResource::collection($this->whenLoaded('resources')),
            'assignments' => AssignmentResource::collection($this->whenLoaded('assignments')),
            'progress' => new LessonProgressResource($this->whenLoaded('progress')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

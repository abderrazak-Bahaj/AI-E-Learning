<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Assignment */
final class AssignmentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'time_limit' => $this->time_limit,
            'max_attempts' => $this->max_attempts,
            'total_points' => $this->total_points,
            'passing_score' => $this->passing_score,
            'status' => $this->status,
            'course_id' => $this->course_id,
            'lesson_id' => $this->lesson_id,
            'questions' => AssignmentQuestionResource::collection($this->whenLoaded('questions')),
            'questions_count' => $this->whenCounted('questions'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

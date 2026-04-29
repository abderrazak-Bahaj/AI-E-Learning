<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin LessonProgress */
final class LessonProgressResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'watch_time' => $this->watch_time,
            'last_position' => $this->last_position,
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'lesson_id' => $this->lesson_id,
            'course_id' => $this->course_id,
        ];
    }
}

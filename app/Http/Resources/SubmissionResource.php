<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Submission */
final class SubmissionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'attempt_number' => $this->attempt_number,
            'score' => $this->score,
            'is_passed' => $this->is_passed,
            'feedback' => $this->feedback,
            'status' => $this->status,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'student' => new UserResource($this->whenLoaded('student')),
            'assignment' => new AssignmentResource($this->whenLoaded('assignment')),
            'answers' => SubmissionAnswerResource::collection($this->whenLoaded('answers')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Student */
final class StudentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'enrollment_status' => $this->enrollment_status,
            'education_level' => $this->education_level,
            'major' => $this->major,
            'interests' => $this->interests,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'learning_preferences' => $this->learning_preferences,
            'gpa' => $this->gpa,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

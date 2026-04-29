<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Enrollment */
final class EnrollmentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'progress' => $this->progress,
            'enrolled_at' => $this->enrolled_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'student' => new UserResource($this->whenLoaded('student')),
            'course' => new CourseResource($this->whenLoaded('course')),
            'certificate' => new CertificateResource($this->whenLoaded('certificate')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

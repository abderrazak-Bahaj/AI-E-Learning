<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Certificate */
final class CertificateResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'certificate_number' => $this->certificate_number,
            'status' => $this->status,
            'certificate_url' => $this->certificate_url,
            'metadata' => $this->metadata,
            'issue_date' => $this->issue_date?->toIso8601String(),
            'generated_at' => $this->generated_at?->toIso8601String(),
            'student' => new UserResource($this->whenLoaded('student')),
            'course' => new CourseResource($this->whenLoaded('course')),
            'enrollment' => new EnrollmentResource($this->whenLoaded('enrollment')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Teacher */
final class TeacherResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'specialization' => $this->specialization,
            'qualification' => $this->qualification,
            'expertise' => $this->expertise,
            'education' => $this->education,
            'certifications' => $this->certifications,
            'rating' => $this->rating,
            'years_of_experience' => $this->years_of_experience,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
final class UserResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'phone' => $this->phone,
            'address' => $this->address,
            'bio' => $this->bio,
            'role' => $this->role,
            'status' => $this->status,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'last_login_at' => $this->last_login_at?->toIso8601String(),
            'profile' => $this->when(
                $this->relationLoaded('admin') || $this->relationLoaded('teacher') || $this->relationLoaded('student'),
                fn () => match ($this->role) {
                    'admin' => new AdminResource($this->whenLoaded('admin')),
                    'teacher' => new TeacherResource($this->whenLoaded('teacher')),
                    'student' => new StudentResource($this->whenLoaded('student')),
                    default => null,
                }
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

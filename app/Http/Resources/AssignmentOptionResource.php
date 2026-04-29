<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\AssignmentOption;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AssignmentOption */
final class AssignmentOptionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'option_text' => $this->option_text,
            'order' => $this->order,
            // Only expose is_correct to teachers/admins
            'is_correct' => $this->when(
                $request->user()?->isTeacher() || $request->user()?->isAdmin(),
                $this->is_correct
            ),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\AssignmentQuestion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AssignmentQuestion */
final class AssignmentQuestionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'question_text' => $this->question_text,
            'question_type' => $this->question_type,
            'points' => $this->points,
            'order' => $this->order,
            'explanation' => $this->when(
                $request->user()?->isTeacher() || $request->user()?->isAdmin(),
                $this->explanation
            ),
            'options' => AssignmentOptionResource::collection($this->whenLoaded('options')),
        ];
    }
}

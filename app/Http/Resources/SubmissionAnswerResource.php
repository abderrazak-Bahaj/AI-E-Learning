<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\SubmissionAnswer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin SubmissionAnswer */
final class SubmissionAnswerResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'question_id' => $this->question_id,
            'selected_option_id' => $this->selected_option_id,
            'answer' => $this->answer,
            'is_correct' => $this->is_correct,
            'score' => $this->score,
            'feedback' => $this->feedback,
            'question' => new AssignmentQuestionResource($this->whenLoaded('question')),
            'selected_option' => new AssignmentOptionResource($this->whenLoaded('selectedOption')),
        ];
    }
}

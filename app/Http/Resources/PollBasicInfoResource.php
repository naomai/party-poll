<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PollBasicInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'owner' => new UserSummaryResource($this->owner),
            'title' => $this->title,
            'question_count' => $this->questions->count(),
            'participant_count' => $this->pollParticipants->count(),
            'links' => [
                'summary' => $this->getUrlSummaryAttribute(),
            ]
        ];
    }

    public function getUrlSummaryAttribute(): string {
        return route("polls.show", $this->id);
    }
}
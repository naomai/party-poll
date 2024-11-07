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
            'member_count' => $this->memberships->count(),
            'links' => [
                'summary' => $this->getUrlSummaryAttribute(),
            ]
        ];
    }

    public function getUrlSummaryAttribute(): string {
        return route("api.polls.show", $this->id);
    }
}

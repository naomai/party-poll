<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        $owner = new UserSummaryResource(User::find($this->owner_id));
        $answer = 

        return [
            'sequence_id' => $this->poll_sequence_id,
            'title' => $this->title,
            'type' => $this->type,
            'response_params' => $this->response_params,
            'owner' => $owner,
            'answer' => $answer,
        ];
    }
}

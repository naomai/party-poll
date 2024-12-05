<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PollSummaryResource extends PollBasicInfoResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        $base = parent::toArray($request);
        $extension = [
            'rules' => [
                'wait_for_everybody' => $this->wait_for_everybody,
                'enable_revise_response' => $this->enable_revise_response,
                'show_question_results' => $this->show_question_results,
                'show_question_answers' => $this->show_question_answers,
                'show_end_results' => $this->show_end_results,
                'show_end_answers' => $this->show_end_answers,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'links' => [
                'questions' => $this->getUrlQuestionsAttribute(),
                'state' => $this->getUrlStateAttribute(),
                'invite' => $this->getUrlInvitation(),
            ],
            
        ];

        return array_merge($base, $extension);
    }

    
    public function getUrlQuestionsAttribute(): string {
        return route("api.poll.questions", $this->id);
    }
    public function getUrlStateAttribute(): string {
        return route("api.poll.state", $this->id);
    }

    public function getUrlInvitation(): ?string {
        if(!$this->enable_link_invite) {
            return null;
        }
        return route("invite.view", ['poll'=>$this->id, 'accesskey'=>$this->access_link_token]);
    }
}

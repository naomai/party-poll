<?php

namespace App\Http\Resources;

use App\Models\Answer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        $owner = new UserSummaryResource(User::find($this->owner_id));
        $answer = Answer::where([
            ['user_id','=', Auth::user()->id],
            ['question_id', '=', $this->id],
        ])->first();

        return [
            'id' => $this->id,
            'sequence_id' => $this->poll_sequence_id,
            'question' => $this->question,
            'type' => $this->type,
            'response_params' => $this->response_params,
            'owner' => $owner,
            'answer' => $answer,
            'revealed' => $this->revealed,
        ];
    }
}

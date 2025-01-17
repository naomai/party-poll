<?php

namespace App\Http\Resources;

use App\Models\Answer;
use App\Models\User;
use App\Services\QuestionService;
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
        $user = Auth::user();

        $answer=null;
        if($user!==null) {
            $answer = Answer::where([
                ['user_id','=', $user->id],
                ['question_id', '=', $this->id],
            ])->first();
        }

        $answersTotal = null;
        $answerStats = null;

        if($this->poll->show_question_results) {
            $answersTotal = $this->answers->count();
            $answerStats = QuestionService::getQuestionStats($this->resource);
        }

        return [
            'id' => $this->id,
            'poll_sequence_id' => $this->poll_sequence_id,
            'question' => $this->question,
            'type' => $this->type,
            'response_params' => $this->response_params,
            'owner' => $owner,
            'answer' => $answer!==null ? $answer->response : null,
            'revealed' => $this->revealed,
            //'answers_total' => $answersTotal,
            'stats' => $answerStats
        ];
    }
}

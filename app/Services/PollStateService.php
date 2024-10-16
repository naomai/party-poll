<?php
namespace App\Services;

use App\Models\Answer;
use App\Models\Poll;
use App\Models\PollParticipant;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PollStateService {
    public static function getPollParticipation(Poll $poll, User $user): PollParticipant {
        return PollParticipant::where([
            ['poll_id', '=', $poll->id],
            ['user_id', '=', $user->id],
        ])->first();
    }

    public static function getCurrentQuestion(PollParticipant $participation): Question {
        /** @var Poll */
        $poll = $participation->poll;
        $isWaitForAll = $poll->wait_for_everybody;
        $userId = $participation->user->id;

        $questionStates = static::getQuestionAnswerPairs($participation);
        
        if($isWaitForAll) {
            $questions = static::getPollQuestions($participation);
            $sequenceId = $poll->sequence_id;
            $question = $questions
                ->where('sequence_id', "=", $sequenceId)
                ->first();
        } else {
            $firstWithoutAnswer = $questionStates->first(
                fn($state) => $state['answer']==null
            );
            $question = $firstWithoutAnswer['question'];
        }

        return $question;
    }

    public static function getPollQuestions(PollParticipant $participation): Builder {
        return Question::where('poll_id', "=", $participation->poll->id)
            ->orderBy('poll_sequence_id');
    }

    public static function getQuestionAnswerPairs(PollParticipant $participation): Collection {
        $questions = static::getPollQuestions($participation)->get();
        $answers = Answer::where([
            ['user_id', "=", $participation->user->id],
        ])->get();

        return $questions->map(
            fn($question)=>[
                'question'=>$question,
                'answer'=>$answers->where('question_id', '=', $question->id)->first()
            ]
        );

    }

    public static function getAccessibleQuestions(PollParticipant $participation): Collection {
        
        $questions = static::getPollQuestions($participation)->get();
    }
}
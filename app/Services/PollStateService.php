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

    public static function getCurrentQuestion(PollParticipant $participation): ?Question {
        $question = static::getAccessibleQuestions($participation)->last();
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

    public static function getAccessibleQuestions(PollParticipant $participation): ?Collection {
        /** @var Poll */
        $poll = $participation->poll;
        $isStarted = $poll->published_sequence_id !== null;
        $isWaitForAll = $poll->wait_for_everybody;

        if(!$isStarted) {
            return collect([]);
        }
        
        $questions = static::getPollQuestions($participation)
            ->where(
                'poll_sequence_id', "<=", $poll->published_sequence_id
            );

        if($isWaitForAll) {
            $sequenceId = $poll->sequence_id;
        } else {
            $questionStates = static::getQuestionAnswerPairs($participation);

            $firstWithoutAnswer = $questionStates->first(
                fn($state) => $state['answer']==null
            );
            $sequenceId = $firstWithoutAnswer['question']->poll_sequence_id;
        }

        if($sequenceId === null) {
            return null;
        }

        return $questions->where('poll_sequence_id', '<=', $sequenceId)->get();
    }

}
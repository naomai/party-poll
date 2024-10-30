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

    public static function getAllowedActions(PollParticipant $participation): Array {
        /** @var Poll */
        $poll = $participation->poll;
        $pollStarted = $poll->sequence_id !== null;

        $pollClosed = 
            ($poll->close_after_start || $poll->wait_for_everybody) 
            && $pollStarted;

        $actions = [
            'modify_poll' => $participation->can_modify_poll,
            'control_flow' => $participation->can_control_flow,
            'see_progress' => $participation->can_see_progress,
            'answer' => $participation->can_answer,

            //derived permissions

            'see_all_questions' => (
                $participation->can_modify_poll ||
                $participation->can_control_flow ||
                $participation->can_see_progress
            ),
            'invite' => (
                $poll->enable_link_invite && !$pollClosed
            )

        ];
        return $actions;
    }

    public static function getUserState(PollParticipant $participation): array {
        $poll = $participation->poll;

        $pollState = static::getPollState($poll);
        $currentQuestion = static::getCurrentQuestion($participation);
        $currentQuestionId = $currentQuestion!==null ? $currentQuestion->id : null;

        return [
            'waiting_start' => !$pollState['started'],
            'waiting_others' => $pollState['blocking'],
            'more_questions' => $pollState['more_questions'],
            'current_question' => $currentQuestionId,

            'poll_state' => $pollState,
        ];
        
    }

    public static function getPollState(Poll $poll): array {
        $pollStarted = $poll->sequence_id !== null;

        $blocking = false;
        $blockingQuestion = static::getBlockingQuestion($poll);

        if($blockingQuestion!==null) {
            $peopleCount = $poll->pollParticipants->count();
            $answersCount = $blockingQuestion->answers->count();
            $blocking = $answersCount < $peopleCount;
        }

        $moreQuestions = static::getNextQuestion($poll) !== null;
        

        return [
            'started' => $pollStarted,
            'blocking' => $blocking,
            'more_questions' => $moreQuestions,
        ];
    }

    public static function refreshState(Poll $poll): void {
        if($poll->wait_for_everybody) {
            $state = static::getPollState($poll);
            if($state['started'] && !$state['blocking'] && $state['more_questions']) {
                static::advanceSequence($poll);
            }
        }
    }

    public static function advanceSequence(Poll $poll): void {
        if(!$poll->wait_for_everybody) {
            return;
        }
        
        $next = static::getNextQuestion($poll);

        if($next !== null) {
            $poll->sequence_id = $next->poll_sequence_id;
            $poll->save();
        }
    }

    public static function getBlockingQuestion(Poll $poll): ?Question {
        $blockingQuestion = null;
        $waitForAll = $poll->wait_for_everybody;
        $pollStarted = $poll->sequence_id !== null;

        if($waitForAll && $pollStarted) {
            $blockingQuestion = 
                static::getQuestionSequence($poll)
                ->where('poll_sequence_id', '<=', $poll->sequence_id)
                ->get()->last();
        }
        return $blockingQuestion;
    }

    public static function getNextQuestion(Poll $poll): ?Question {
        $nextQuestion = null;
        $waitForAll = $poll->wait_for_everybody;
        $sequenceId = $poll->sequence_id;
        $sequenceIdPub = $poll->published_sequence_id;
        if($sequenceId === null) {
            $sequenceId = 0;
        }

        if($waitForAll) {
            $nextQuestion =
                static::getQuestionSequence($poll)
                ->where([
                    ['poll_sequence_id', '>', $sequenceId],
                    ['poll_sequence_id', '<=', $sequenceIdPub],
                ])
                ->get()->first();
        }
        return $nextQuestion;
    }
    

    public static function getCurrentQuestion(PollParticipant $participation): ?Question {
        $question = static::getAccessibleQuestions($participation)->last();
        if($question->answers->where('user_id', '=', $participation->user->id)->count() != 0) {
            return null;
        }
        return $question;
    }

    public static function getQuestionSequence(Poll $poll): Builder {

        return Question::where('poll_id', "=", $poll->id)
            ->orderBy('poll_sequence_id');
    }

    public static function getQuestionAnswerPairs(PollParticipant $participation): Collection {
        $questions = static::getQuestionSequence($participation->poll)->get();
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
        
        $questions = static::getQuestionSequence($poll)
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
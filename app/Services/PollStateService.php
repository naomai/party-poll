<?php
namespace App\Services;

use App\Models\Answer;
use App\Models\Poll;
use App\Models\Membership;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PollStateService {
    public static function getMembership(Poll $poll, User $user): Membership {
        return Membership::where([
            ['poll_id', '=', $poll->id],
            ['user_id', '=', $user->id],
        ])->first();
    }

    public static function getAllowedActions(Membership $membership): Array {
        /** @var Poll */
        $poll = $membership->poll;
        $pollStarted = $poll->sequence_id !== null;

        $pollClosed = 
            ($poll->close_after_start || $poll->wait_for_everybody) 
            && $pollStarted;

        $actions = [
            'modify_poll' => $membership->can_modify_poll,
            'control_flow' => $membership->can_control_flow,
            'see_progress' => $membership->can_see_progress,
            'answer' => $membership->can_answer,

            //derived permissions

            'see_all_questions' => (
                $membership->can_modify_poll ||
                $membership->can_control_flow ||
                $membership->can_see_progress
            ),
            'invite' => (
                $poll->enable_link_invite && !$pollClosed
            )

        ];
        return $actions;
    }

    public static function getUserState(Membership $membership): array {
        $poll = $membership->poll;

        $pollState = static::getPollState($poll);
        $currentQuestion = static::getCurrentQuestion($membership);
        $currentQuestionId = $currentQuestion!==null ? $currentQuestion->id : null;

        $othersResponsesLeft = $poll->memberships->count() - $currentQuestion->answers->count();

        return [
            'waiting_start' => !$pollState['started'],
            'waiting_me' => $currentQuestionId !== null,
            'waiting_others' => $pollState['blocking'],
            'more_questions' => $pollState['more_questions'],
            'question_id' => $currentQuestionId,
            'blocking_id' => $pollState['blocking_id'],
            'others_responses_left' => $othersResponsesLeft,
            'poll_state' => $pollState,
        ];
        
    }

    public static function getPollState(Poll $poll): array {
        $pollStarted = $poll->sequence_id !== null;

        $blocking = false;
        $blockingQuestion = static::getBlockingQuestion($poll);

        if($blockingQuestion!==null) {
            $peopleCount = $poll->memberships->count();
            $answersCount = $blockingQuestion->answers->count();
            $blocking = $answersCount < $peopleCount;
        }

        $moreQuestions = static::getNextQuestion($poll) !== null;
        

        return [
            'started' => $pollStarted,
            'blocking' => $blocking,
            'blocking_id' => $blockingQuestion->id,
            'more_questions' => $moreQuestions,
            'published_seq' => $poll->published_sequence_id,
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
            $poll->timestamps = false;
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
    

    public static function getCurrentQuestion(Membership $membership): ?Question {
        $question = static::getAccessibleQuestions($membership)->last();
        /*if($question->answers->where('user_id', '=', $membership->user->id)->count() != 0) {
            return null;
        }*/
        return $question;
    }

    public static function getQuestionSequence(Poll $poll): Builder {

        return Question::where('poll_id', "=", $poll->id)
            ->orderBy('poll_sequence_id');
    }

    public static function getQuestionAnswerPairs(Membership $membership): Collection {
        $questions = static::getQuestionSequence($membership->poll)->get();
        $answers = Answer::where([
            ['user_id', "=", $membership->user->id],
        ])->get();

        return $questions->map(
            fn($question)=>[
                'question'=>$question,
                'answer'=>$answers->where('question_id', '=', $question->id)->first()
            ]
        );

    }

    public static function getAccessibleQuestions(Membership $membership): ?Collection {
        /** @var Poll */
        $poll = $membership->poll;
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
            $questionStates = static::getQuestionAnswerPairs($membership);

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
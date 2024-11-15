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

    /** getMemberState
     * Get member-specific state of a poll flow
     */
    public static function getMemberState(Membership $membership): array {
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

    /** getPollState
     * Get global state of a poll flow
     */
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

    /** ensureStateValidity
     * Validate poll state, and perform recovery when invalid.
     */
    public static function ensureStateValidity(Poll $poll): void {
        if($poll->wait_for_everybody) {
            $state = static::getPollState($poll);
            if($state['started'] && !$state['blocking'] && $state['more_questions']) {
                static::advanceSynchronousPoll($poll);
            }
        }
    }

    /** advanceSynchronousPoll
     * Advances synchronous poll to next question. Does nothing if 
     * poll is asynchronous, or if already reached last published question.
     */
    public static function advanceSynchronousPoll(Poll $poll): void {
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

    /** getBlockingQuestion
     * Get question that's blocking synchronous poll from advancing.
     */
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

    /** getNextQuestion
     * Get next question to be presented to member, limited to
     * published questions.
     * 
     * @return ?Question next question in sequence, or null if already reached
     *   last published question
     */
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
    

    /** getCurrentQuestion
     * Get unanswered/blocking question for the member
     * This question is either waiting for response from member, or blocking 
     * while waiting for others.
     */
    public static function getCurrentQuestion(Membership $membership): ?Question {
        $question = static::getAccessibleQuestions($membership)->last();
        /*if($question->answers->where('user_id', '=', $membership->user->id)->count() != 0) {
            return null;
        }*/
        return $question;
    }

    /** getQuestionSequence
     * Get query builder for all poll questions, ordered by poll-sequence
     */
    public static function getQuestionSequence(Poll $poll): Builder {

        return Question::where('poll_id', "=", $poll->id)
            ->orderBy('poll_sequence_id');
    }

    /** getQuestionAnswerPairsSequence
     * Gets collection of all poll questions paired with member answers.
     * The pairs are ordered (by poll-sequence)
     */
    public static function getQuestionAnswerPairsSequence(Membership $membership): Collection {
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

    /** getAccessibleQuestions
     * Gets collection of all questions currently accesible to a member
     * Depending on poll type, fetch questions up to:
     * - Synchronous: blocking question that's waiting for answers from others
     * - Asynchronous: first unanswered by the member
     */
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
            $questionStates = static::getQuestionAnswerPairsSequence($membership);

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
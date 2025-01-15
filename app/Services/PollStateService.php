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

        $currentQuestionId = null;
        $othersResponsesLeft = 0;
        
        if($currentQuestion!==null) {
            $currentQuestionId = $currentQuestion->id;
            $othersResponsesLeft = $poll->memberships->count() - $currentQuestion->answers->count();
        }

        $started = $pollState['started'];

        return [
            'waiting_start' => !$started,
            'waiting_me' => $started && $pollState['more_questions'] && $currentQuestionId !== null,
            'waiting_others' => $started && $pollState['blocking'],
            'more_questions' => $pollState['more_questions'],
            'question_id' => $currentQuestionId,
            'blocking_id' => $pollState['blocking_id'],
            'others_responses_left' => $othersResponsesLeft,
            'poll_state' => $pollState,
            //'finished' => $started && !$pollState['more_questions'] && $currentQuestion===null,
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
            'blocking_id' => $blocking ? $blockingQuestion->id : null,
            'more_questions' => $moreQuestions,
            'published_seq' => $poll->published_sequence_id,
        ];
    }

    /** ensureStateValidity
     * Validate poll state, and perform recovery when invalid.
     */
    public static function ensureStateValidity(Poll $poll): void {
        self::fixSequenceId($poll);

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

    /** fixSequenceId
     * Ensures that all poll questions have valid SequenceIds,
     * ie. they start at 1, and increase continuously
     * Additionally, ensure valid SequenceId references in Poll record
     */
    private static function fixSequenceId($poll): void {
        $questions = $poll->questions->sortBy('poll_sequence_id')->all();

        $publishedSeqId = $poll->published_sequence_id;
        $currentSeqId = $poll->sequence_id;
        
        $validSeqId = 1;
        foreach($questions as $question) {
            $questionSeqId = $question->poll_sequence_id;
            if($questionSeqId != $validSeqId) {
                $question->poll_sequence_id = $validSeqId;
                if($publishedSeqId == $questionSeqId) {
                    $poll->published_sequence_id = $validSeqId;
                }
                if($currentSeqId == $questionSeqId) {
                    $poll->sequence_id = $validSeqId;
                }
                $question->save();
            }
            $validSeqId++;
        }

        if($poll->published_sequence_id >= $validSeqId) {
            $poll->published_sequence_id = $validSeqId;
        }
        if($poll->sequence_id >= $validSeqId) {
            $poll->sequence_id = $validSeqId;
        }

        $poll->save();
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
     *   last published question (also if poll is yet to be started)
     */
    public static function getNextQuestion(Poll $poll): ?Question {
        $nextQuestion = null;
        $waitForAll = $poll->wait_for_everybody;
        $sequenceId = $poll->sequence_id;
        $sequenceIdPub = $poll->published_sequence_id;

        if($sequenceId === null) {
            return null;
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
     * while waiting for others. Returns null if poll is not started, or
     * member answered all the questions.
     */
    public static function getCurrentQuestion(Membership $membership): ?Question {
        //$question = static::getAccessibleQuestions($membership)->last();
        $questionPair = static::getQuestionAnswerPairsSequence($membership)
            ->filter(fn($q)=>$q['answer']===null)->first();

        if($questionPair===null) {
            return null;
        }
        $question=$questionPair['question'];
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
     * - Asynchronous: all published questions
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
            $sequenceId = null;
            $questionStates = static::getQuestionAnswerPairsSequence($membership);

            /*$firstWithoutAnswer = $questionStates->first(
                fn($state) => $state['answer']==null
            );

            if($firstWithoutAnswer!==null) {
                // there is an unanswered question
                $sequenceId = $firstWithoutAnswer['question']->poll_sequence_id;
            }else{
                // all questions answered
                $sequenceId = 999999;
            }*/
            $sequenceId = $poll->published_sequence_id;
        }
        if($sequenceId === null) {
            return collect([]);
        }

        return $questions->where('poll_sequence_id', '<=', $sequenceId)->get();
    }

}
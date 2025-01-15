<?php
namespace App\Services;

use App\Events\QuestionsPublishedEvent;
use App\Http\Resources\PollBasicInfoResource;
use App\Http\Resources\PollSummaryResource;
use App\Http\Resources\QuestionResource;
use App\Models\Poll;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PollManagementService {
    public function index(): ResourceCollection {
        Gate::authorize('index', Poll::class);

        $list = $this->getAllPollsForUser()->get();
        return PollBasicInfoResource::collection($list);
    }

    public function store(Array $pollData) : JsonResource {
        Gate::authorize('store', Poll::class);

        $poll = Poll::make($pollData);
        $poll->owner_id = Auth::user()->id;
        $poll->save();

        //$this->regenerateInvitationToken($poll);
        
        return new PollSummaryResource($poll);
    }

    public function show(Poll $poll): Array {
        Gate::authorize('view', $poll);

        PollStateService::ensureStateValidity($poll);

        $user = Auth::user();
        $membership = MembershipService::getMembership($poll, $user);

        $response = [];

        $response['info'] = new PollSummaryResource($poll);
        $response['state'] = PollStateService::getMemberState($membership);
        $response['questions'] = self::getQuestionList($poll);
        $response['questions_privileged'] = self::getQuestionListPrivileged($poll);
        $response['membership'] = MembershipService::getAllowedActions($membership); 

        return $response;
    }

    public function update(Poll $poll, Array $pollData): JsonResource {
        Gate::authorize('update', $poll);

        $poll->update($pollData);
        return new PollSummaryResource($poll);
    }

    public function destroy(string $id) {
        //
    }

    public function publishQuestions(Poll $poll) {
        PollStateService::ensureStateValidity($poll);
        $previousSequenceId = $poll->published_sequence_id;
        $maxSequenceId = $poll->questions->max('poll_sequence_id');
        $poll->published_sequence_id = $maxSequenceId;
        if($poll->sequence_id === null && $maxSequenceId !== null) {
            $poll->sequence_id = 1;
        }
        $poll->save();

        // EVENT
        $publishedQuestions = $poll->questions->where('poll_sequence_id', '>', $previousSequenceId)->all();

        QuestionsPublishedEvent::dispatch($poll, $publishedQuestions);
    }

    public function regenerateInvitationToken(Poll $poll) : string {
        $tokenNew = self::generateInvitationToken();

        $poll->access_link_token = $tokenNew;
        $poll->save();
        return $tokenNew;
    }

    public static function generateInvitationToken(): string {
        // faker uses mt_rand internally, which is not cryptographically safe
        $randomWords = fake()->words(3, false);
        return "lorem-ipsum-" . implode("-", $randomWords);
    }

    private static function getQuestionList(Poll $poll) {
        $user = Auth::user();
        $membership = MembershipService::getMembership($poll, $user);        

        $questions = PollStateService::getAccessibleQuestions($membership);
        $questions = $questions->map(function($q) { 
            $q->revealed = true;
            return $q;
        });
        
        return QuestionResource::collection($questions);
    }

    private static function getQuestionListPrivileged(Poll $poll) {
        $user = Auth::user();
        $membership = MembershipService::getMembership($poll, $user);
        
        $canSeeAll = 
            $membership->can_control_flow ||
            $membership->can_see_progress ||
            $membership->can_modify_poll;

        $questions = [];

        if($canSeeAll) {
            $questions = $poll->questions->all();
        }
        return QuestionResource::collection($questions);
    }

    private function getAllPollsForUser() {
        return Poll::whereRelation("users", 'user_id', "=", Auth::user()->id);
    }
}
<?php
namespace App\Services;

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

        $this->regenerateInvitationToken($poll);
        
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

    public function regenerateInvitationToken(Poll $poll) : string {
        // faker uses mt_rand internally, which is not cryptographically safe
        $randomWords = fake()->words(3, false);
        $tokenNew = "lorem-ipsum-" . implode("-", $randomWords);

        $poll->access_link_token = $tokenNew;
        $poll->save();
        return $tokenNew;
    }

    private static function getQuestionList(Poll $poll) {
        $user = Auth::user();
        $membership = MembershipService::getMembership($poll, $user);
        
        $canSeeAll = 
            $membership->can_control_flow ||
            $membership->can_see_progress ||
            $membership->can_modify_poll;

        if($canSeeAll) {
            $lastSeqId = $poll->sequence_id;
            $questions = $poll->questions->map(function($q) use($lastSeqId) { 
                $q->revealed = $q->poll_sequence_id <= $lastSeqId;
                return $q;
            });
            
        }else{
            $questions = PollStateService::getAccessibleQuestions($membership);
            $questions = $questions->map(function($q) { 
                $q->revealed = true;
                return $q;
            });
        }
        return QuestionResource::collection($questions);
    }

    private function getAllPollsForUser() {
        return Poll::whereRelation("users", 'user_id', "=", Auth::user()->id);
    }
}
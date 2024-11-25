<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionStoreRequest;
use App\Http\Requests\QuestionUpdateRequest;
use App\Models\Poll;
use App\Models\Question;
use App\Services\MembershipService;
use App\Services\PollStateService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionManagementController extends Controller {
    use AuthorizesRequests;
    
    public function index(Poll $poll, PollStateService $service): JsonResponse {
        //$this->authorize('index', Question::class);
        $user = Auth::user();
        $membership = MembershipService::getMembership($poll, $user);

        $canSeeAll = 
            $membership->can_control_flow ||
            $membership->can_see_progress ||
            $membership->can_modify_poll;

        if($canSeeAll) {
            $questions = $poll->questions->toArray();
        }else{
            $questions = $service->getAccessibleQuestions($membership)->toArray();
        }
        return response()->json($questions);
    }



    
    public function store(Poll $poll, QuestionStoreRequest $request) {
        $this->authorize('update', $poll);

        $user = Auth::user();

        $validated = $request->validated();

        $question = Question::make($validated);
        $question->poll_id = $poll->id;
        $question->owner_id = $user->id;
        $question->save();

        return $question;
    }
    
    public function show(Poll $poll, Question $question): JsonResponse {
        $this->authorize('view', $question);
    
        return response()->json($question);
    }

    
    public function update(Poll $poll, Question $question, QuestionUpdateRequest $request) {
        $this->authorize('update', $question);

        $validated = $request->validated();

        $question->update($validated);

        return $question;
    }

    
    public function destroy(Poll $poll, Question $question) {
        $this->authorize('delete', $question);
        $question->delete();
    }


}

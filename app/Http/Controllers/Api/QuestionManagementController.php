<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\Question;
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
        $membership = $service->getMembership($poll, $user);

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



    
    public function store(Request $request) {
        //
    }

    
    public function show(Poll $poll, Question $question): JsonResponse {
        $this->authorize('view', $question);
    
        return response()->json($question);
    }

    
    public function update(Request $request, string $id) {
        //
    }

    
    public function destroy(string $id) {
        //
    }


}

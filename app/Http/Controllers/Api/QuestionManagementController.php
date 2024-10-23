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
        $participation = $service->getPollParticipation($poll, $user);

        $canSeeAll = 
            $participation->can_control_flow ||
            $participation->can_see_progress ||
            $participation->can_modify_poll;

        if($canSeeAll) {
            $questions = $poll->questions;
        }else{
            $questions = $service->getAccessibleQuestions($participation);
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

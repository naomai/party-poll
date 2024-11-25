<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionStoreRequest;
use App\Models\Poll;
use App\Models\Question;
use App\Services\MembershipService;
use App\Services\PollStateService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class QuestionController extends Controller {
    use AuthorizesRequests;
    
    public function index(Poll $poll, PollStateService $service): Response {
        $this->authorize('view', $poll);
        $user = Auth::user();
        $membership = MembershipService::getMembership($poll, $user);

        $canSeeAll = 
            $membership->can_control_flow ||
            $membership->can_see_progress ||
            $membership->can_modify_poll;

        if($canSeeAll) {
            $lastSeqId = $poll->sequence_id;
            $questions = $poll->questions->map(function($q) use($lastSeqId) { 
                $q['revealed'] = $q->poll_sequence_id <= $lastSeqId;
                return $q;
            });
            
        }else{
            $questions = $service->getAccessibleQuestions($membership)->toArray();
        }
        


        return Inertia::render('questions.index', [
            'questions'=>$questions
        ]);
    }



    
    public function store(Poll $poll, QuestionStoreRequest $request) {
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

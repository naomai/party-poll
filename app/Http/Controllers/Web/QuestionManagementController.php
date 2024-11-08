<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\Question;
use App\Services\PollStateService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class QuestionManagementController extends Controller {
    use AuthorizesRequests;
    
    public function index(Poll $poll, PollStateService $service): Response {
        //$this->authorize('index', Question::class);
        $user = Auth::user();
        $participation = $service->getPollParticipation($poll, $user);

        $canSeeAll = 
            $participation->can_control_flow ||
            $participation->can_see_progress ||
            $participation->can_modify_poll;

        if($canSeeAll) {
            $lastSeqId = $poll->sequence_id;
            $questions = $poll->questions->map(function($q) use($lastSeqId) { 
                $q['revealed'] = $q->poll_sequence_id <= $lastSeqId;
                return $q;
            });
            
        }else{
            $questions = $service->getAccessibleQuestions($participation)->toArray();
        }
        


        return Inertia::render('questions.index', [
            'questions'=>$questions
        ]);
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

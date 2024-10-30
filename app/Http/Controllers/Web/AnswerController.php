<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Poll;
use App\Models\Question;
use App\Services\QuestionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AnswerController extends Controller {
    use AuthorizesRequests;

    public function store(
        Question $question, 
        Request $request, 
        QuestionService $svc
    ) {
        $svc->storeAnswer($request, $question);
        
        return to_route("polls.show", [
            'poll'=>$question->poll->id
        ]); 
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id) {
        //
    }
}

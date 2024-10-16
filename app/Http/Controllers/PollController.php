<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\User;
use App\Services\PollStateService;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class PollController extends Controller {
    public function list(): JsonResponse {
        $list = $this->getAllPollsForUser()->get();

        return response()->json($list);
    }

    public function state(Poll $poll, PollStateService $service): JsonResponse {
        $user = Auth::user();
        
        $participation = $service->getPollParticipation($poll, $user);
        $currentQuestion = $service->getCurrentQuestion($participation);

        $state = [
            "participation"    => $participation,
            "current_question" => $currentQuestion,
        ];

        return response()->json($state);
    }

    public function get(Poll $poll): JsonResponse {
        return response()->json($poll);
    }

    public function questions(Poll $poll): JsonResponse {
        return response()->json($poll->questions);
    }


    private function getAllPollsForUser() {
        return Poll::whereRelation("users", 'user_id', "=", Auth::user()->id);
    }
}

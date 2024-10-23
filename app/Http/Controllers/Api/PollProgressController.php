<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\User;
use App\Services\PollStateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PollProgressController extends Controller {


    public function view(Poll $poll, PollStateService $service): JsonResponse {
        $user = Auth::user();
        
        $participation = $service->getPollParticipation($poll, $user);
        $currentQuestion = $service->getCurrentQuestion($participation);

        $state = [
            "participation"    => $participation,
            "current_question" => $currentQuestion,
        ];

        return response()->json($state);
    }


}

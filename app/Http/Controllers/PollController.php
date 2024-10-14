<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PollController extends Controller {
    public function list(): JsonResponse {
        $list = $this->getAllPollsForUser()->get();

        return response()->json($list);
    }

    public function get(string $id): JsonResponse {
        $pollInfo = $this->getAllPollsForUser()->find($id);
        // todo 404/403
        return response()->json($pollInfo);
    }

    public function questions(string $id): JsonResponse {
        $pollInfo = $this->getAllPollsForUser()->find($id);

        // todo 404/403
        return response()->json($pollInfo->questions);
    }

    private function getAllPollsForUser() {
        return Poll::whereRelation("users", 'user_id', "=", Auth::user()->id);
    }
}

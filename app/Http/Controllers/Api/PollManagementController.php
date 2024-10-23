<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PollInfoUpdateRequest;
use App\Http\Resources\PollBasicInfoResource;
use App\Http\Resources\PollSummaryResource;
use App\Models\Poll;
use App\Models\User;
use App\Services\PollStateService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class PollManagementController extends Controller {
    use AuthorizesRequests;

    public function index(): JsonResponse {
        $this->authorize('index', Poll::class);
        $list = $this->getAllPollsForUser()->get();

        return response()
            ->json(PollBasicInfoResource::collection($list));
    }

    public function store(PollInfoUpdateRequest $request) : JsonResponse {
        $this->authorize('store', Poll::class);

        $poll = Poll::make($request->validated());
        $poll->owner_id = Auth::user()->id;
        $poll->save();
        return response()->json($poll);
    }

    public function show(Poll $poll): JsonResponse {
        $this->authorize('view', $poll);
        return response()
            ->json(new PollSummaryResource($poll));
    }

    public function update(PollInfoUpdateRequest $request, Poll $poll) {
        $this->authorize('update', Poll::class);
        $poll->update($request->validated());
        return response()->json($poll);
    }

    public function destroy(string $id) {
        //
    }

    private function getAllPollsForUser() {
        return Poll::whereRelation("users", 'user_id', "=", Auth::user()->id);
    }
}

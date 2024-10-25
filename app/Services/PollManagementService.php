<?php
namespace App\Services;

use App\Http\Resources\PollBasicInfoResource;
use App\Http\Resources\PollSummaryResource;
use App\Models\Poll;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PollManagementService {
    public function index(): ResourceCollection {
        Gate::authorize('index', Poll::class);

        $list = $this->getAllPollsForUser()->get();
        return PollBasicInfoResource::collection($list);
    }

    public function store(Array $pollData) : JsonResource {
        Gate::authorize('store', Poll::class);

        $poll = Poll::make($pollData);
        $poll->owner_id = Auth::user()->id;
        $poll->save();
        return new PollSummaryResource($poll);
    }

    public function show(Poll $poll): JsonResource {
        Gate::authorize('view', $poll);

        return new PollSummaryResource($poll);
    }

    public function update(Poll $poll, Array $pollData): JsonResource {
        Gate::authorize('update', Poll::class);

        $poll->update($pollData);
        return new PollSummaryResource($poll);
    }

    public function destroy(string $id) {
        //
    }

    private function getAllPollsForUser() {
        return Poll::whereRelation("users", 'user_id', "=", Auth::user()->id);
    }
}
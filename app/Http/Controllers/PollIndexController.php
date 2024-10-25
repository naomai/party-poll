<?php

namespace App\Http\Controllers;

use App\Http\Resources\PollBasicInfoResource;
use App\Models\Poll;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PollIndexController extends Controller {
    use AuthorizesRequests;

    public function view(User $user) {
        $this->authorize('index', Poll::class);
        $list = $this->getAllPollsForUser()->get();
        return Inertia::render("PollIndex/Index", [
            'polls' => PollBasicInfoResource::collection($list),
        ]);
    }

    private function getAllPollsForUser() {
        return Poll::whereRelation("users", 'user_id', "=", Auth::user()->id);
    }
}

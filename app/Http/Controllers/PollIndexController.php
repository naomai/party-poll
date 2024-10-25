<?php

namespace App\Http\Controllers;

use App\Services\PollManagementService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class PollIndexController extends Controller {
    use AuthorizesRequests;

    public function view(PollManagementService $manage) {
        $pollList = $manage->index();
        return Inertia::render("PollIndex/Index", [
            'polls' => $pollList,
        ]);
    }

}

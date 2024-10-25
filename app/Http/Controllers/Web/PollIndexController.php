<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PollManagementService;
use Inertia\Inertia;

class PollIndexController extends Controller {
    public function view(PollManagementService $manage) {
        $pollList = $manage->index();
        return Inertia::render("PollIndex/Index", [
            'polls' => $pollList,
        ]);
    }

}

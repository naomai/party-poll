<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\Poll;
use App\Services\MembershipService;
use App\Services\PollManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class InvitationController extends Controller {
    public function view(
            Poll $poll, 
            Request $request,
            MembershipService $svc
        ) {

        $token = $request->query("accesskey");

        $allowJoining = $svc->checkInvitation($poll, $token);

        // invalid token / poll does not accept invitations
        if(!$allowJoining) {
            return Inertia::render("Invite/Denied", [
                'poll'=>$poll,
            ])->toResponse($request)->setStatusCode(403);
        }

        // save invitation token and redirect to guest account creation
        if(!Auth::check()) {
            Session::put('invite_poll', $poll->id);
            Session::put('invite_token', $token);
            return redirect(route('index'));

        }

        $user = Auth::user();

        $svc->create($poll, $user);

        return redirect(route('polls.show', [
            'poll' => $poll->id,
        ]));
    }
}

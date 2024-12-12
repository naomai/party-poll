<?php

namespace App\Http\Controllers\Web;

use App\Enums\InvitationStatus;
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

        $invitationStatus = $svc->checkInvitation($poll, $token);

        
        if($invitationStatus == InvitationStatus::Invalid) {
            // invalid token / poll does not accept invitations
            return Inertia::render("Invite/Denied", [
                'status' => $invitationStatus,
            ])->toResponse($request)->setStatusCode(403);
            
        } elseif($invitationStatus == InvitationStatus::Expired) {
            // expired - poll has started
            return Inertia::render("Invite/Denied", [
                'status' => $invitationStatus,
                'poll' => $poll,
            ])->toResponse($request)->setStatusCode(410);
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

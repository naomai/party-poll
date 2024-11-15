<?php
namespace App\Services;

use App\Models\Membership;
use App\Models\Poll;
use App\Models\User;

class MembershipService {
    
    /** getMembership
     * Get membership object tying User to a Poll
     */
    public static function getMembership(Poll $poll, User $user): Membership {
        return Membership::where([
            ['poll_id', '=', $poll->id],
            ['user_id', '=', $user->id],
        ])->first();
    }

    /** getAllowedActions
     * Get permissions descriptor for a member
     */
    public static function getAllowedActions(Membership $membership): Array {
        /** @var Poll */
        $poll = $membership->poll;
        $pollStarted = $poll->sequence_id !== null;

        $pollClosed = 
            ($poll->close_after_start || $poll->wait_for_everybody) 
            && $pollStarted;

        $actions = [
            'modify_poll' => $membership->can_modify_poll,
            'control_flow' => $membership->can_control_flow,
            'see_progress' => $membership->can_see_progress,
            'answer' => $membership->can_answer,

            //derived permissions

            'see_all_questions' => (
                $membership->can_modify_poll ||
                $membership->can_control_flow ||
                $membership->can_see_progress
            ),
            'invite' => (
                $poll->enable_link_invite && !$pollClosed
            )

        ];
        return $actions;
    }


}

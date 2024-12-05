<?php
namespace App\Services;

use App\Models\Membership;
use App\Models\Poll;
use App\Models\User;

class MembershipService {
    public function create(
        Poll $poll, 
        User $user, 
        array $blueprint = self::BLUEPRINT_USER
    ) : Membership {
        $membership = $poll->memberships()->where(
            'user_id', '=', $user->id
        )->first();
        if($membership !== null) {
            return $membership;
        }
        $memberProps = $blueprint;

        $memberProps['poll_id'] = $poll->id;
        $memberProps['user_id'] = $user->id;

        $membership = Membership::make($memberProps);
        $membership->save();
        return $membership;
    }
    
    /** getMembership
     * Get membership object tying User to a Poll
     */
    public static function getMembership(Poll $poll, User $user): ?Membership {
        /*return Membership::where([
            ['poll_id', '=', $poll->id],
            ['user_id', '=', $user->id],
        ])->first();*/
        return $poll->memberships()->where('user_id', '=', $user->id)->first();
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

    public function checkInvitation(Poll $poll, ?string $token) : bool {
        $tokenMatch = $token !== null && $token == $poll->access_link_token;

        $isPollStarted = $poll->sequence_id !== null;
        $startGuard = $poll->close_after_start && $isPollStarted;

        return $poll->enable_link_invite && !$startGuard && $tokenMatch;
    }

    public const BLUEPRINT_USER = [
        "can_modify_poll" => false,
        "can_control_flow" => false,
        "can_see_progress" => false,
        "can_answer" => true,
    ];

    public const BLUEPRINT_OWNER = [
        "can_modify_poll" => true,
        "can_control_flow" => true,
        "can_see_progress" => true,
        "can_answer" => true,
    ];

}

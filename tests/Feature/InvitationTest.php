<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PollManagementService;
use App\Services\PollStateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    public function test_accept_invitation(): void {
        [$poll, $user] = $this->_prepareInvitationConditions();
        $invitationToken = $poll->access_link_token;

        $this->assertFalse($poll->hasMember($user), "user is not poll member before consuming invitation");

        $this->actingAs($user)
            ->get(route('invite.view', [
                'poll'=>$poll->id, 
                'accesskey'=>$invitationToken,
            ]))
            ->assertRedirectToRoute('polls.show', ['poll'=>$poll->id])
        ;

        $this->assertTrue($poll->hasMember($user), "user is poll member after consuming invitation");
    }

    public function test_accept_invitation_fail_on_started_poll(): void {
        [$poll, $user] = $this->_prepareInvitationConditions();
        $invitationToken = $poll->access_link_token;

        Helpers::createQuestionsForPoll($poll);
        $manage = $this->app->make(PollManagementService::class);
        $manage->publishQuestions($poll);


        $this->actingAs($user)
            ->get(route('invite.view', [
                'poll'=>$poll->id, 
                'accesskey'=>$invitationToken,
            ]))
            ->assertForbidden()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Invite/Denied')
            )
        ;
    }

    private function _prepareInvitationConditions(): array {
        [$poll, $user] = Helpers::createPollWithAdmin([
            'enable_link_invite' => true,
        ]);

        $otherUser = User::factory()->create();

        return [$poll, $otherUser];
    }

}

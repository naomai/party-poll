<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\MembershipService;
use App\Services\PollManagementService;
use App\Services\PollStateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class InvitationTest extends TestCase {

    public function test_start_conditions() : void {
        [$poll, $user] = $this->_prepareInvitationConditions();

        $this->assertTrue($poll->enable_link_invite, "test poll has invitations enabled");
        $this->assertTrue($poll->close_after_start, "test poll closes invitations after starting");
        $this->assertStringStartsWith("lorem-ipsum-", $poll->access_link_token, "test poll has valid invite token");
        
        $this->assertInstanceOf(User::class, $user, "test user is being created");
        $this->assertIsNumeric($user->id, "test user record is in database");
    }

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

    public function test_redirect_when_already_member(): void {
        [$poll, $user] = $this->_prepareInvitationConditions();
        $invitationToken = $poll->access_link_token;

        $membershipSvc = $this->app->make(MembershipService::class);
        $membershipSvc->create($poll, $user);

        $this->assertTrue($poll->hasMember($user), "user is poll member");

        $this->actingAs($user)
        ->get(route('invite.view', [
            'poll'=>$poll->id, 
            'accesskey'=>$invitationToken,
        ]))
        ->assertRedirectToRoute('polls.show', ['poll'=>$poll->id])
    ;
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
            ->assertGone()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Invite/Denied')
                ->where("status", 2)
                ->has("poll")
            )
        ;
    }

    public function test_accept_invitation_fail_on_invalid_token(): void {
        [$poll, $user] = $this->_prepareInvitationConditions();
        $invitationToken = "lorem-ipsum-invalid-invalid";

        $this->actingAs($user)
            ->get(route('invite.view', [
                'poll'=>$poll->id, 
                'accesskey'=>$invitationToken,
            ]))
            ->assertForbidden()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Invite/Denied')
                ->where("status", 1)
                ->missing("poll")
            )
        ;

        $invitationToken = "invalid-invalid-invalid";

        $this->actingAs($user)
            ->get(route('invite.view', [
                'poll'=>$poll->id, 
                'accesskey'=>$invitationToken,
            ]))
            ->assertForbidden()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Invite/Denied')
                ->where("status", 1)
                ->missing("poll")
            )
        ;

        $this->actingAs($user)
            ->get(route('invite.view', [
                'poll'=>$poll->id,
            ]))
            ->assertForbidden()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Invite/Denied')
                ->where("status", 1)
                ->missing("poll")
            )
        ;
    }

    public function test_redirect_anonymous_to_name_creation(): void {
        [$poll, $user] = Helpers::createPollWithAdmin([
            'enable_link_invite' => true,
        ]);
        $invitationToken = $poll->access_link_token;

        $this
            ->get(route('invite.view', [
                'poll'=>$poll->id, 
                'accesskey'=>$invitationToken,
            ]))
            ->assertRedirectToRoute('index')
        ;
    }

    // --

    private function _prepareInvitationConditions(): array {
        [$poll, $user] = Helpers::createPollWithAdmin([
            'enable_link_invite' => true,
            'close_after_start' => true,
        ]);

        $otherUser = User::factory()->create();

        return [$poll, $otherUser];
    }

}

<?php

namespace Tests\Feature\Authz;

use App\Models\Poll;
use App\Models\User;
use Database\Seeders\PollSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Feature\Helpers;

class PollTest extends TestCase {
    //use RefreshDatabase;

    public function setUp(): void {
        parent::setUp();
    }

    public function test_list_polls(): void {
        $user = User::factory()->create();
        $pollCount = 3;

        Poll::factory()->count($pollCount)->create(['owner_id'=>$user->id]);

        $this->actingAs($user)
            ->get(route('polls.index'))
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('PollIndex/Index')
                ->has('polls', $pollCount)
            );
    }
    public function test_create_poll(): void {
        $user = User::factory()->create();
        
        $properties = [
            'title'=>fake()->sentence(),
            'wait_for_everybody'=> fake()->boolean(),
        ];

        $this->actingAs($user)
            ->post(route('polls.store'), $properties)
            ->assertRedirect();

        $this->assertTrue($user->polls()->get()->contains('title', $properties['title']));
    }

    public function test_edit_poll(): void {
        [$poll, $user] = Helpers::createPollWithAdmin();

        $newProperties = [
            'title'=>fake()->sentence(),
            'wait_for_everybody'=> !$poll->wait_for_everybody,
        ];

        $this->actingAs($user)
            ->patch(route('polls.update', $poll->id), $newProperties)
            ->assertNoContent();

        $poll->refresh();
        $this->assertSame($newProperties['title'], $poll->title);
        $this->assertEquals($newProperties['wait_for_everybody'], $poll->wait_for_everybody);
    }

    public function test_view_poll(): void {
        [$poll, $user] = Helpers::createPollWithAdmin();

        $this->actingAs($user)
            ->get(route('polls.show', $poll->id))
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Poll/Summary')
                ->has('info', fn (Assert $page) => $page
                    ->where('id', $poll->id)
                    ->where('title', $poll->title)
                    ->has('owner', fn (Assert $page) => $page
                        ->where('id', $user->id)
                        ->has('name')
                    )
                    ->has('links')
                    ->has('rules')
                    ->has('question_count')
                    ->has('member_count')
                    ->etc()
                )
            );
        
    }

    public function test_get_invitation_link(): void {
        [$pollWithInvite, $user] = Helpers::createPollWithAdmin([
            'enable_link_invite' => true,
        ]);
        $invitationToken = $pollWithInvite->access_link_token;

        $this->assertStringStartsWith("lorem-ipsum-", $invitationToken);
        $this->actingAs($user)
            ->get(route('polls.show', $pollWithInvite->id))
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Poll/Summary')
                ->where('info.links.invite', fn (string $url) =>
                    strpos($url, $invitationToken) !== false
                )
        );


        $pollNoInvite = Helpers::createPollFor($user, [
            'enable_link_invite' => false
        ]);
        $this->actingAs($user)
            ->get(route('polls.show', $pollNoInvite->id))
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Poll/Summary')
                ->where('info.links.invite', null)
        );
    }

    // --

    public function test_new_poll_has_valid_properties(): void {
        [$poll, $user] = Helpers::createPollWithAdmin(['enable_link_invite'=>true]);

        $this->assertNull($poll->sequence_id);
        $this->assertNull($poll->published_sequence_id);

        $this->assertIsString($poll->access_link_token, "has invite token string");
        $this->assertStringContainsString(
            "lorem-ipsum-", $poll->access_link_token, 
            "has valid invite token"
        );
    }

    public function test_owner_has_membership(): void {
        [$poll, $user] = Helpers::createPollWithAdmin();

        $this->assertTrue($poll->hasMember($user), "poll owner is a member of poll");
    }

}

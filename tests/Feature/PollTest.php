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

    public function test_owner_has_membership(): void {
        $user = User::factory()->create();
        $poll = Poll::factory()->create(['owner_id'=>$user->id]);

        $this->assertTrue($poll->hasMember($user), "poll owner is a member of poll");
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
        $user = User::factory()->create();
        $poll = Poll::factory()->create(['owner_id'=>$user->id]);

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



}

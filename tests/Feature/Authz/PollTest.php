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

class PollTest extends TestCase {
    use RefreshDatabase;

    public function setUp(): void {
        parent::setUp();
        
        $this->seed(UserSeeder::class);
        $this->seed(PollSeeder::class);
        
    }

    public function test_list_poll(): void {
        $poll = Poll::all()->random();
        $user = $this->getPollMuggle($poll);

        $pollCount = $user->polls->count();

        $this->actingAs($user)
            ->getJson(route('polls.index'))
            ->assertStatus(200)
            ->assertJsonCount($pollCount)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'title',
                    'owner' => ['id', 'name'],
                    'links' => ['summary'],
                ],
            ]);
    }

    public function test_access_poll_info_muggle(): void {
        $poll = Poll::all()->random();
        $user = $this->getPollMuggle($poll);

        $this->actingAs($user)
            ->getJson(route('polls.show', ['poll'=>$poll->id]))
            ->assertStatus(200);
    }

    public function test_access_single_question_muggle(): void {
        $poll = Poll::all()->random();
        $user = $this->getPollMuggle($poll);
        $question = $poll->questions->random();

        $this->actingAs($user)
            ->getJson(route('poll.question.get', ['poll'=>$poll->id, 'question'=>$question->id]))
            ->assertStatus(200);
    }



    private function getPollMuggle(Poll $poll) : User {
        $user = $poll->users()->where([
            ['can_modify_poll', '=', 'false'],
            ['can_control_flow', '=', 'false'],
            ['can_see_progress', '=', 'false'],
        ])->get()->random();
        return $user;
    }

    private function getPollWizard(Poll $poll) : User {
        $user = $poll->users
            ->  where('can_modify_poll', '=', 'true')
            ->orWhere('can_control_flow', '=', 'true')
            ->orWhere('can_see_progress', '=', 'true')
            ->get()->random();
        return $user;
    }
}

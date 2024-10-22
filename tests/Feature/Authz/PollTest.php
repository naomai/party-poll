<?php

namespace Tests\Feature\Authz;

use App\Models\Poll;
use App\Models\User;
use Database\Seeders\PollSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PollTest extends TestCase {
    use RefreshDatabase;

    public function test_access_single_question(): void {
        $this->seed(UserSeeder::class);
        $this->seed(PollSeeder::class);
        
        $poll = Poll::all()->random();
        $user = $poll->users->random();
        $question = $poll->questions->random();

        $this->actingAs($user)
            ->get(route('poll.question.get', $question->id))
            ->assertStatus(200);
    }
}

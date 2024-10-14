<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Poll;
use App\Models\Question;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PollSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        Poll::factory()
            ->has(Question::factory()->count(5))
            ->create();
    }
}

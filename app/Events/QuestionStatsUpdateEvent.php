<?php

namespace App\Events;

use App\Models\Question;
use App\Services\QuestionService;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuestionStatsUpdateEvent implements ShouldBroadcast, ShouldDispatchAfterCommit {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Question $question) {
        //
    }

    public function broadcastWith(): array {
        $stats = QuestionService::getQuestionStats($this->question);
        return [
            'question_id'=>$this->question->id,
            'stats'=>$stats,
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array {
        return [
            new PrivateChannel('Poll.'.$this->question->poll->id),
        ];
    }

    public function broadcastAs(): string{
        return 'question.stats';
    }
}

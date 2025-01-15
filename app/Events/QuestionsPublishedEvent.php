<?php

namespace App\Events;

use App\Http\Resources\PollBasicInfoResource;
use App\Http\Resources\QuestionResource;
use App\Models\Poll;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class QuestionsPublishedEvent implements ShouldBroadcast, ShouldDispatchAfterCommit {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Poll $poll, public array $questions) {
        //
    }

    public function broadcastWith(): array {
        return [
            'poll' => new PollBasicInfoResource($this->poll),
            'questions'=>QuestionResource::collection($this->questions),
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array {
        return [
            new PrivateChannel('Poll.'.$this->poll->id),
        ];
    }

    public function broadcastAs(): string {
        return 'poll.publish';
    }
}

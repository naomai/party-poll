<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Poll extends Model {
    use HasFactory;

    protected $appends = ['url_questions', 'url_state'];

    public static function booted(): void {
        self::created(function(Poll $poll){
            PollParticipant::create([
                'poll_id' => $poll->id,
                'user_id' => $poll->owner_id,
                'can_modify_poll' =>    1,
                'can_control_flow' =>   1,
                'can_see_progress' =>   1,
            ]);
        });
    }

    public function questions(): HasMany {
        return $this->hasMany(Question::class);
    }

    public function owner(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function participations(): HasMany {
        return $this->hasMany(PollParticipant::class);
    }

    public function users(): HasManyThrough {
        return $this->hasManyThrough(
            User::class, PollParticipant::class, 
            'poll_id', 'id',
            'id', 'user_id'
        );
    }

    // ------

    public function getUserParticipation(User $user): ?PollParticipant {
        $particiaption = $this->participations
            ->where('user_id', '=', $user->id)
            ->first();
        return $particiaption;
    }

    public function hasParticipant(User $user): bool {
        $particiaption = $this->getUserParticipation($user);
        return $particiaption !== null;
    }

    public function getUrlQuestionsAttribute(): string {
        return route("poll.question.list", $this->id);
    }
    public function getUrlStateAttribute(): string {
        return route("poll.state", $this->id);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Poll extends Model {
    use HasFactory;

    protected $fillable = [
        'title',
        'enable_link_invite',
        'close_after_start',
        'wait_for_everybody',
        'enable_revise_response',
        'show_question_results',
        'show_question_answers',
        'show_end_results',
        'show_end_answers',
    ];

    public static function booted(): void {
        self::created(function(Poll $poll){
            Model::unguard();
            PollParticipant::create([
                'poll_id' => $poll->id,
                'user_id' => $poll->owner_id,
                'can_modify_poll' =>    1,
                'can_control_flow' =>   1,
                'can_see_progress' =>   1,
            ]);
            Model::reguard();
        });
    }

    public function questions(): HasMany {
        return $this->hasMany(Question::class);
    }

    public function owner(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function pollParticipants(): HasMany {
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
        $participation = $this->pollParticipants
            ->where('user_id', '=', $user->id)
            ->first();
        return $participation;
    }

    public function hasParticipant(User $user): bool {
        $participation = $this->getUserParticipation($user);
        return $participation !== null;
    }

}

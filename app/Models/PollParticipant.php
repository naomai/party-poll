<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PollParticipant extends Model {
    use HasFactory;

    protected $hidden = [
        'poll', 'user', 'updated_at',
        /* 'id', 'user_id', */
    ];

    public function poll(): BelongsTo {
        return $this->belongsTo(Poll::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array {
        return [
            'can_modify_poll' => 'boolean',
            'can_control_flow' => 'boolean',
            'can_see_progress' => 'boolean',
            'can_answer' => 'boolean',

        ];
    }

}

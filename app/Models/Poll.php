<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Poll extends Model {
    use HasFactory;

    public function questions(): HasMany {
        return $this->hasMany(Question::class);
    }

    public function owner(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function users(): HasManyThrough {
        return $this->hasManyThrough(
            User::class, PollParticipant::class, 
            'poll_id', 'id',
            'id', 'user_id'
        );
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PollParticipant extends Model {
    use HasFactory;

    public function poll(): BelongsTo {
        return $this->belongsTo(Poll::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}

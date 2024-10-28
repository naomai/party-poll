<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model {
    use HasFactory;

    public function question(): BelongsTo {
        return $this->belongsTo(Question::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function response(): Attribute {
        return Attribute::make(
            get: fn (string $json) => json_decode($json, true),
            set: fn (array $obj) => json_encode($obj),
        );
    }
}

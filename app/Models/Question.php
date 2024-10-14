<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model {
    use HasFactory;

    public function poll(): BelongsTo {
        return $this->belongsTo(Poll::class);
    }

    public function owner(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    protected function responseParams(): Attribute {
        return Attribute::make(
            get: fn (string $json) => json_decode($json),
            set: fn (array $obj) => json_encode($obj),
        );
    }
}

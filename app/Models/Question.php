<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model {
    use HasFactory;

    public $fillable = ['question', 'response_params', 'type'];

    public static function booting() {
        self::creating(function(Question $question) {
            $seqIdMax = Question::where('poll_id', '=', $question->poll_id)
                ->max('poll_sequence_id'); 
            $question->poll_sequence_id = $seqIdMax + 1;
            return true;
        });
    }

    public function poll(): BelongsTo {
        return $this->belongsTo(Poll::class);
    }

    public function owner(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany {
        return $this->hasMany(Answer::class);
    }
    
    /*
    protected function responseParams(): Attribute {
        return Attribute::make(
            get: fn (string $json) => json_decode($json, true),
            set: fn (array $obj) => json_encode($obj),
        );
    }*/

    protected function casts(): array {
        return [
            'response_params' => 'object',
        ];
    }

}

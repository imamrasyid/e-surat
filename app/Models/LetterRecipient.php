<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterRecipient extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'letter_id',
        'from_user_id',
        'from_position_id',
        'to_position_id',
        'instruction',
        'received_at',
        'read_at',
    ];

    public function letter()
    {
        return $this->belongsTo(Letter::class);
    }
}

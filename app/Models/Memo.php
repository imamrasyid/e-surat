<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Memo extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'letter_id',
        'letter_recipient_id',
        'is_cc',
        'from_user_id',
        'from_position_id',
        'to_position_id',
        'instruction',
        'followup',
        'received_at',
        'read_at',
    ];

    public function letter()
    {
        return $this->belongsTo(Letter::class);
    }

    public function recipient()
    {
        return $this->belongsTo(LetterRecipient::class, 'letter_recipient_id');
    }

    public function fromPosition()
    {
        return $this->belongsTo(Position::class, 'from_position_id');
    }

    public function toPosition()
    {
        return $this->belongsTo(Position::class, 'to_position_id');
    }

    public function senderUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }
}

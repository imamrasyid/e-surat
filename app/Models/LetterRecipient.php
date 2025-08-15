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

    public function dispositions()
    {
        return $this->hasMany(Disposition::class)
            ->with(['fromPosition:id,name', 'toPosition:id,name'])
            ->orderBy('received_at');
    }

    public function fromPosition()
    {
        return $this->belongsTo(Position::class, 'from_position_id');
    }

    public function toPosition()
    {
        return $this->belongsTo(Position::class, 'to_position_id');
    }
}

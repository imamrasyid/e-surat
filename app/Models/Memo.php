<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Memo extends Model
{
    use SoftDeletes;

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

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_cc' => 'boolean',
            'received_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(LetterRecipient::class, 'letter_recipient_id');
    }

    public function fromPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'from_position_id');
    }

    public function toPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'to_position_id');
    }

    public function senderUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }
}

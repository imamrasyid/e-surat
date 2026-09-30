<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $letter_id
 * @property int $letter_recipient_id
 * @property bool $is_cc
 * @property int $from_user_id
 * @property int $from_position_id
 * @property int $to_position_id
 * @property string|null $instruction
 * @property string|null $followup
 * @property Carbon|null $received_at
 * @property Carbon|null $read_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Letter|null $letter
 * @property-read LetterRecipient|null $recipient
 * @property-read Position|null $fromPosition
 * @property-read Position|null $toPosition
 * @property-read User|null $senderUser
 */
#[Fillable([
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
])]
class Memo extends Model
{
    use SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_cc' => 'boolean',
            'received_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Letter, $this>
     */
    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class);
    }

    /**
     * @return BelongsTo<LetterRecipient, $this>
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(LetterRecipient::class, 'letter_recipient_id');
    }

    /**
     * @return BelongsTo<Position, $this>
     */
    public function fromPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'from_position_id');
    }

    /**
     * @return BelongsTo<Position, $this>
     */
    public function toPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'to_position_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function senderUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }
}

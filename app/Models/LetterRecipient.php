<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $letter_id
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
 * @property-read Position|null $fromPosition
 * @property-read Position|null $toPosition
 */
#[Fillable([
    'letter_id',
    'from_user_id',
    'from_position_id',
    'to_position_id',
    'instruction',
    'followup',
    'received_at',
    'read_at',
])]
class LetterRecipient extends Model
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
     * @return HasMany<Disposition, $this>
     */
    public function activityDispo(): HasMany
    {
        return $this->hasMany(Disposition::class)
            ->select('id', 'letter_recipient_id')
            ->orderBy('received_at');
    }

    /**
     * @return HasMany<Memo, $this>
     */
    public function activityMemo(): HasMany
    {
        return $this->hasMany(Memo::class)
            ->select('id', 'letter_recipient_id')
            ->orderBy('received_at');
    }

    /**
     * @return HasMany<Disposition, $this>
     */
    public function dispositions(): HasMany
    {
        return $this->hasMany(Disposition::class)
            ->with(['fromPosition:id,name', 'toPosition:id,name'])
            ->orderBy('received_at');
    }

    /**
     * @return HasMany<Memo, $this>
     */
    public function memos(): HasMany
    {
        return $this->hasMany(Memo::class)
            ->with(['fromPosition:id,name', 'toPosition:id,name'])
            ->orderBy('received_at');
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
}

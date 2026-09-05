<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
            'received_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class);
    }

    public function activityDispo(): HasMany
    {
        return $this->hasMany(Disposition::class)
            ->select('id', 'letter_recipient_id')
            ->orderBy('received_at');
    }

    public function activityMemo(): HasMany
    {
        return $this->hasMany(Memo::class)
            ->select('id', 'letter_recipient_id')
            ->orderBy('received_at');
    }

    public function dispositions(): HasMany
    {
        return $this->hasMany(Disposition::class)
            ->with(['fromPosition:id,name', 'toPosition:id,name'])
            ->orderBy('received_at');
    }

    public function memos(): HasMany
    {
        return $this->hasMany(Memo::class)
            ->with(['fromPosition:id,name', 'toPosition:id,name'])
            ->orderBy('received_at');
    }

    public function fromPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'from_position_id');
    }

    public function toPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'to_position_id');
    }
}

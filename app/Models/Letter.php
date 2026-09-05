<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Letter extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'letter_id',
        'sender',
        'number',
        'date',
        'receipt_date',
        'subject',
        'category',
        'agenda',
        'type',
        'status',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'receipt_date' => 'datetime',
        ];
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(LetterRecipient::class)
            ->with(['fromPosition:id,name', 'toPosition:id,name'])
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

    public function docs(): HasMany
    {
        return $this->hasMany(LetterDoc::class)->where('is_active', true);
    }

    public function senderUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

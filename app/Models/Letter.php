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
 * @property string $letter_id
 * @property string $sender
 * @property string|null $number
 * @property Carbon $date
 * @property Carbon $receipt_date
 * @property string $subject
 * @property string $category
 * @property string $security_classification
 * @property string $received_via
 * @property string $agenda
 * @property string $type
 * @property string $status
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User|null $senderUser
 */
#[Fillable([
    'letter_id',
    'sender',
    'number',
    'date',
    'receipt_date',
    'subject',
    'category',
    'security_classification',
    'received_via',
    'agenda',
    'type',
    'status',
    'created_by',
])]
class Letter extends Model
{
    use SoftDeletes;

    /**
     * @return HasMany<LetterRecipient, $this>
     */
    public function recipients(): HasMany
    {
        return $this->hasMany(LetterRecipient::class)
            ->with(['fromPosition:id,name', 'toPosition:id,name'])
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
     * @return HasMany<LetterDoc, $this>
     */
    public function docs(): HasMany
    {
        return $this->hasMany(LetterDoc::class)->where('is_active', true);
    }

    /**
     * @return HasMany<LetterActivity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(LetterActivity::class)->orderBy('created_at');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function senderUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

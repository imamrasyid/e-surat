<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    
    public function recipients()
    {
        return $this->hasMany(LetterRecipient::class)
            ->with(['fromPosition:id,name', 'toPosition:id,name'])
            ->orderBy('received_at');
    }

    public function dispositions()
    {
        return $this->hasMany(Disposition::class)
            ->with(['fromPosition:id,name', 'toPosition:id,name'])
            ->orderBy('received_at');
    }

    public function memos()
    {
        return $this->hasMany(Memo::class)
            ->with(['fromPosition:id,name', 'toPosition:id,name'])
            ->orderBy('received_at');
    }

    public function docs()
    {
        return $this->hasMany(LetterDoc::class)->where('is_active', true);
    }

    public function senderUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

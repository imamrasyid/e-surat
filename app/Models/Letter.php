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
        return $this->hasMany(LetterRecipient::class);
    }

    public function docs()
    {
        return $this->hasMany(LetterDoc::class);
    }
}

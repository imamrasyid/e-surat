<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterDoc extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'letter_id',
        'name',
        'url',
        'is_active',
    ];

    public function letter()
    {
        return $this->belongsTo(Letter::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelHasSpecialDisposition extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'to_position_id',
        'is_active',
        'granted_at',
        'revoked_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'granted_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function model()
    {
        return $this->morphTo();
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'to_position_id');
    }
}

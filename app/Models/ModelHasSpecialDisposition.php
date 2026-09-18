<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'granted_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'to_position_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Position extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'parent_id',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'parent_id');
    }
    /**
     * Get the parent position's name.
     *
     * @return string|null
     */
    public function getParentNameAttribute(): ?string
    {
        return $this->parent ? $this->parent->name : null;
    }

    public function users(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'model', 'model_has_position')
            ->withPivot(['start_date', 'end_date', 'is_active'])
            ->withTimestamps();
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(LetterRecipient::class, 'to_position_id');
    }

    public function dispositions(): HasMany
    {
        return $this->hasMany(Disposition::class, 'to_position_id');
    }

    public function memos(): HasMany
    {
        return $this->hasMany(Memo::class, 'to_position_id');
    }
}

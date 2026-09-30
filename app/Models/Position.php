<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property int|null $parent_id
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string|null $parent_name
 * @property-read Position|null $parent
 */
#[Fillable(['name', 'parent_id', 'is_active'])]
class Position extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Position, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'parent_id');
    }

    /**
     * Get the parent position's name.
     */
    public function getParentNameAttribute(): ?string
    {
        return $this->parent ? $this->parent->name : null;
    }

    /**
     * @return MorphToMany<User, $this>
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'model', 'model_has_position')
            ->withPivot(['start_date', 'end_date', 'is_active'])
            ->withTimestamps();
    }

    /**
     * @return HasMany<LetterRecipient, $this>
     */
    public function recipients(): HasMany
    {
        return $this->hasMany(LetterRecipient::class, 'to_position_id');
    }

    /**
     * @return HasMany<Disposition, $this>
     */
    public function dispositions(): HasMany
    {
        return $this->hasMany(Disposition::class, 'to_position_id');
    }

    /**
     * @return HasMany<Memo, $this>
     */
    public function memos(): HasMany
    {
        return $this->hasMany(Memo::class, 'to_position_id');
    }
}

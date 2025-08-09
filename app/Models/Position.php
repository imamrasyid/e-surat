<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function parent()
    {
        return $this->belongsTo(Position::class, 'parent_id');
    }
    /**
     * Get the parent position's name.
     *
     * @return string|null
     */
    public function getParentNameAttribute()
    {
        return $this->parent ? $this->parent->name : null;
    }

    public function users()
    {
        return $this->morphedByMany(User::class, 'model', 'model_has_position')
                    ->withPivot(['start_date', 'end_date', 'is_active'])
                    ->withTimestamps();
    }
}

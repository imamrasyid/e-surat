<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefKdKlasifikasi extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ref_kode_klasifikasi_arsip';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'kode',
        'nama',
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

    public function agendaSurats(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RefAgendaSurat::class);
    }
}

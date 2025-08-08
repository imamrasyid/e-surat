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
}

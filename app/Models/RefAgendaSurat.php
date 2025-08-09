<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefAgendaSurat extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ref_agenda_surat';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'klasifikasi_id', 'nomor_urut', 'tahun', 'nomor_agenda'
    ];

    public function klasifikasi()
    {
        return $this->belongsTo(RefKdKlasifikasi::class);
    }
}

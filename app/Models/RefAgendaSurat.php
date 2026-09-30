<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $klasifikasi_id
 * @property int $nomor_urut
 * @property int $tahun
 * @property string $nomor_agenda
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read RefKdKlasifikasi|null $klasifikasi
 */
#[Table('ref_agenda_surat')]
#[Fillable(['klasifikasi_id', 'nomor_urut', 'tahun', 'nomor_agenda'])]
class RefAgendaSurat extends Model
{
    /**
     * @return BelongsTo<RefKdKlasifikasi, $this>
     */
    public function klasifikasi(): BelongsTo
    {
        return $this->belongsTo(RefKdKlasifikasi::class);
    }
}

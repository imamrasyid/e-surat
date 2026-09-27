<?php

namespace App\Actions\MasterData\KodeKlasifikasi;

use App\Models\RefKdKlasifikasi;

class UpdateKodeKlasifikasiAction
{
    /**
     * Update an existing classification code record.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(RefKdKlasifikasi $kode, array $data): bool
    {
        return $kode->update($data);
    }
}

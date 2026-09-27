<?php

namespace App\Actions\MasterData\KodeKlasifikasi;

use App\Models\RefKdKlasifikasi;

class CreateKodeKlasifikasiAction
{
    /**
     * Create a new classification code record.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): RefKdKlasifikasi
    {
        return RefKdKlasifikasi::create($data);
    }
}

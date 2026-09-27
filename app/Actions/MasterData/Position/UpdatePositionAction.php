<?php

namespace App\Actions\MasterData\Position;

use App\Models\Position;

class UpdatePositionAction
{
    /**
     * Update an existing position record.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(Position $position, array $data): bool
    {
        return $position->update($data);
    }
}

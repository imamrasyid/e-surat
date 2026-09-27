<?php

namespace App\Actions\MasterData\Position;

use App\Models\Position;

class CreatePositionAction
{
    /**
     * Create a new position record.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Position
    {
        return Position::create($data);
    }
}

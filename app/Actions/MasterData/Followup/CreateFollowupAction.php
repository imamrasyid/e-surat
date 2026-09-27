<?php

namespace App\Actions\MasterData\Followup;

use App\Models\Followup;

class CreateFollowupAction
{
    /**
     * Create a new followup record.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Followup
    {
        return Followup::create($data);
    }
}

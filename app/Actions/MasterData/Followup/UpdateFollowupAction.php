<?php

namespace App\Actions\MasterData\Followup;

use App\Models\Followup;

class UpdateFollowupAction
{
    /**
     * Update an existing followup record.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(Followup $followup, array $data): bool
    {
        return $followup->update($data);
    }
}

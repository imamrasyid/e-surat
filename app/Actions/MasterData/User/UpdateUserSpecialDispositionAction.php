<?php

namespace App\Actions\MasterData\User;

use App\Models\User;

class UpdateUserSpecialDispositionAction
{
    /**
     * Update special disposition permissions for a user.
     *
     * @param  array<int, int|string>  $positionIds
     */
    public function handle(User $user, array $positionIds = []): void
    {
        $user->updateSpecialDispositions($positionIds);
    }
}

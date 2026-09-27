<?php

namespace App\Actions\MasterData\User;

use App\Models\User;

class UpdateUserRoleAction
{
    /**
     * Synchronize roles for a user.
     *
     * @param  array<int, int|string>  $roleIds
     */
    public function handle(User $user, array $roleIds = []): User
    {
        return $user->syncRoles($roleIds);
    }
}

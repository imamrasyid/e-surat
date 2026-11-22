<?php

namespace App\Actions\MasterData\User;

use App\Models\User;
use App\Services\PermissionsCache;

class UpdateUserRoleAction
{
    /**
     * Synchronize roles for a user.
     *
     * @param  array<int, int|string>  $roleIds
     */
    public function handle(User $user, array $roleIds = []): User
    {
        $user->syncRoles($roleIds);

        PermissionsCache::forget($user);

        return $user;
    }
}

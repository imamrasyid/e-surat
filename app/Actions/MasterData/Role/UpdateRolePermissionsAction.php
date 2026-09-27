<?php

namespace App\Actions\MasterData\Role;

use Spatie\Permission\Models\Role;

class UpdateRolePermissionsAction
{
    /**
     * Synchronize permissions for a role.
     *
     * @param  array<int, int|string>  $permissionIds
     */
    public function handle(Role $role, array $permissionIds = []): Role
    {
        return $role->syncPermissions($permissionIds);
    }
}

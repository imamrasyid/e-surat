<?php

namespace App\Actions\MasterData\Role;

use App\Services\PermissionsCache;
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
        $role->syncPermissions($permissionIds);

        // Hak akses role berubah: hapus cache permission seluruh pemegang role
        PermissionsCache::forgetRole($role);

        return $role;
    }
}

<?php

namespace App\Actions\MasterData\Role;

use Spatie\Permission\Models\Role;

class UpdateRoleAction
{
    /**
     * Update an existing role record.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(Role $role, array $data): bool
    {
        return $role->update($data);
    }
}

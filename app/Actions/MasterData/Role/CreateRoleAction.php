<?php

namespace App\Actions\MasterData\Role;

use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Models\Role;

class CreateRoleAction
{
    /**
     * Create a new role record.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): RoleContract
    {
        return Role::create($data);
    }
}

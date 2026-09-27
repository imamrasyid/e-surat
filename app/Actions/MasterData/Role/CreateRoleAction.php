<?php

namespace App\Actions\MasterData\Role;

use Spatie\Permission\Models\Role;

class CreateRoleAction
{
    /**
     * Create a new role record.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Role
    {
        return Role::create($data);
    }
}

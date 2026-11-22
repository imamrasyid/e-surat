<?php

namespace App\Actions\MasterData\User;

use App\Models\User;
use App\Services\PermissionsCache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateUserAction
{
    /**
     * Create a new user with default hashed password, its active position, and role.
     *
     * @param  array{name: string, username: string, position_id: int, role_id: int}  $data
     */
    public function handle(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'password' => Hash::make('password'),
            ]);

            $user->positions()->attach((int) $data['position_id'], [
                'start_date' => now(),
                'is_active' => true,
            ]);

            $user->syncRoles([(int) $data['role_id']]);

            PermissionsCache::forget($user);

            return $user;
        });
    }
}

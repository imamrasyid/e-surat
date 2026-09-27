<?php

namespace App\Actions\MasterData\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UpdateUserAction
{
    /**
     * Update an existing user and terminate active sessions if username changed.
     *
     * @param  array{name: string, username: string, is_active: bool, password?: string|null}  $data
     */
    public function handle(User $user, array $data): bool
    {
        $usernameChanged = $data['username'] !== $user->username;

        $updateData = [
            'name' => $data['name'],
            'username' => $data['username'],
            'is_active' => $data['is_active'],
        ];

        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $updated = $user->update($updateData);

        if ($usernameChanged) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        return $updated;
    }
}

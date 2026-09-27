<?php

namespace App\Actions\MasterData\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateUserAction
{
    /**
     * Create a new user record with hashed password.
     *
     * @param  array{name: string, username: string, password: string}  $data
     */
    public function handle(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
        ]);
    }
}

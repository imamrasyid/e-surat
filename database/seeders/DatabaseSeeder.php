<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([Permissions::class]);

        $admin = User::factory()->create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'msbhdn@icloud.com'
        ]);

        $admin->assignRole('administrator');
    }
}

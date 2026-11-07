<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed katalog permission, master data, dan administrator default.
     *
     * Administrator memakai kredensial default yang sama di semua server
     * (username `superadmin`, password `password`). Ganti password setelah
     * login pertama. Penyesuaian master data dilakukan lewat UI Master Data.
     */
    public function run(): void
    {
        $this->call([
            Permissions::class,
            MasterDataSeeder::class,
        ]);

        $admin = User::firstOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Administrator',
                'email' => null,
                'is_active' => true,
                'password' => 'password',
            ],
        );

        if ($admin->email_verified_at === null) {
            $admin->forceFill(['email_verified_at' => now()])->save();
        }

        $admin->assignRole('Super Admin');
    }
}

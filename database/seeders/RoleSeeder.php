<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin — Gate::before handles full bypass
        Role::firstOrCreate(['name' => 'Super Admin']);

        // Pengelola
        $pengelola = Role::firstOrCreate(['name' => 'Pengelola']);
        $pengelola->syncPermissions([
            'compose.create',
            'compose.store',
            'sent.index',
            'sent.show',
            'sent.edit',
            'sent.update',
            'disposition.index',
            'disposition.show',
            'disposition.create',
            'disposition.store',
            'memo.index',
            'memo.show',
            'memo.create',
            'memo.store',
            'memo.close',
            'master.followup',
            'master.klasifikasi',
            'arsip.index',
            'arsip.create',
            'arsip.store',
            'arsip.show',
            'arsip.edit',
            'arsip.update',
            'arsip.delete',
        ]);

        // Pengguna
        $pengguna = Role::firstOrCreate(['name' => 'Pengguna']);
        $pengguna->syncPermissions([
            'primary.index',
            'primary.show',
            'undangan.index',
            'undangan.show',
            'disposition.index',
            'disposition.show',
            'disposition.create',
            'disposition.store',
            'memo.index',
            'memo.show',
            'memo.create',
            'memo.store',
        ]);
    }
}
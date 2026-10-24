<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class Permissions extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'compose.create',
            'compose.store',
            'sent.index',
            'sent.show',
            'sent.edit',
            'sent.update',
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
            'memo.close',
            'master.followup',
            'master.klasifikasi',
            'master.jabatan',
            'master.pengguna',
            'arsip.index',
            'arsip.create',
            'arsip.store',
            'arsip.show',
            'arsip.edit',
            'arsip.update',
            'arsip.delete',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }
    }
}

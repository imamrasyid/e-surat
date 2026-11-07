<?php

namespace Database\Seeders;

use App\Models\Followup;
use App\Models\Position;
use App\Models\RefKdKlasifikasi;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Master data awal yang sama untuk semua perusahaan: katalog role, assignment
 * permission, jabatan, tindak lanjut, dan klasifikasi arsip.
 *
 * Tidak membuat akun. Penyesuaian dilakukan lewat UI Master Data.
 * Seeder ini idempotent sehingga provisioning boleh dijalankan berulang.
 */
class MasterDataSeeder extends Seeder
{
    /**
     * Jalankan seeder.
     */
    public function run(): void
    {
        $this->seedRoles();
        $this->seedPositions();
        $this->seedFollowups();
        $this->seedKlasifikasiArsip();
    }

    /**
     * Role dan assignment permission. Nama role/permission stabil antar server
     * sehingga upgrade tidak memerlukan migration khusus perusahaan.
     */
    private function seedRoles(): void
    {
        // Super Admin — Gate::before handles full bypass.
        Role::firstOrCreate(['name' => 'Super Admin']);

        $pengelola = Role::firstOrCreate(['name' => 'Pengelola']);

        // Assignment default hanya saat role baru dibuat; perubahan lewat UI
        // Master Data tidak boleh ditimpa saat seeder dijalankan ulang.
        if ($pengelola->wasRecentlyCreated) {
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
        }

        $pengguna = Role::firstOrCreate(['name' => 'Pengguna']);

        if ($pengguna->wasRecentlyCreated) {
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

    /**
     * Hirarki jabatan awal. Perusahaan dapat menyesuaikannya lewat master data.
     */
    private function seedPositions(): void
    {
        $pimpinan = Position::firstOrCreate(
            ['name' => 'Pimpinan'],
            ['parent_id' => null, 'is_active' => true],
        );

        $sekretaris = Position::firstOrCreate(
            ['name' => 'Sekretaris'],
            ['parent_id' => $pimpinan->id, 'is_active' => true],
        );

        $kabagUmum = Position::firstOrCreate(
            ['name' => 'Kepala Bagian Umum'],
            ['parent_id' => $sekretaris->id, 'is_active' => true],
        );

        Position::firstOrCreate(
            ['name' => 'Kepala Bidang Data dan Informasi'],
            ['parent_id' => $sekretaris->id, 'is_active' => true],
        );

        Position::firstOrCreate(
            ['name' => 'Staf Umum'],
            ['parent_id' => $kabagUmum->id, 'is_active' => true],
        );

        Position::firstOrCreate(
            ['name' => 'Staf Kearsipan'],
            ['parent_id' => $kabagUmum->id, 'is_active' => true],
        );
    }

    /**
     * Tindak lanjut bawaan untuk disposisi.
     */
    private function seedFollowups(): void
    {
        $followups = [
            'Diperiksa dan Diproses Lebih Lanjut',
            'Dievaluasi dan Dilaporkan',
            'Diteliti/Dikaji',
            'Diberikan Saran/Pendapat',
        ];

        foreach ($followups as $name) {
            Followup::firstOrCreate(['name' => $name], ['is_active' => true]);
        }
    }

    /**
     * Klasifikasi arsip awal.
     */
    private function seedKlasifikasiArsip(): void
    {
        $klasifikasi = [
            '000' => 'Umum',
            '005' => 'Undangan',
            '010' => 'Kepegawaian',
            '020' => 'Keuangan',
            '030' => 'Perencanaan dan Program',
            '040' => 'Perlengkapan',
        ];

        foreach ($klasifikasi as $kode => $nama) {
            RefKdKlasifikasi::firstOrCreate(['kode' => $kode], ['nama' => $nama, 'is_active' => true]);
        }
    }
}

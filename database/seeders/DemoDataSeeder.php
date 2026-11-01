<?php

namespace Database\Seeders;

use App\Models\Followup;
use App\Models\Position;
use App\Models\RefKdKlasifikasi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Data contoh untuk mencoba alur aplikasi: satu akun Pengelola, dua akun Pengguna,
 * beserta jabatan, tindak lanjut, dan klasifikasi arsip pendukung.
 *
 * Jalankan manual, bukan bagian dari DatabaseSeeder:
 * php artisan db:seed --class=DemoDataSeeder
 */
class DemoDataSeeder extends Seeder
{
    /**
     * Jalankan seeder.
     */
    public function run(): void
    {
        $positions = $this->seedPositions();
        $this->seedFollowups();
        $this->seedKlasifikasiArsip();

        $this->seedUser('Pengelola', [
            'name' => 'Rina Pengelola',
            'username' => 'pengelola',
            'email' => 'pengelola@e-surat.test',
        ], $positions['Kepala Bagian Umum']);

        $this->seedUser('Pengguna', [
            'name' => 'Budi Staf Umum',
            'username' => 'pengguna1',
            'email' => 'pengguna1@e-surat.test',
        ], $positions['Staf Umum']);

        $this->seedUser('Pengguna', [
            'name' => 'Sari Staf Kearsipan',
            'username' => 'pengguna2',
            'email' => 'pengguna2@e-surat.test',
        ], $positions['Staf Kearsipan']);

        $this->command->info('Data contoh siap: 1 Pengelola, 2 Pengguna, 6 jabatan, 4 tindak lanjut, 6 klasifikasi arsip.');
    }

    /**
     * Buat hirarki jabatan dan kembalikan model berdasarkan nama.
     *
     * @return array<string, Position>
     */
    private function seedPositions(): array
    {
        $pimpinan = Position::updateOrCreate(
            ['name' => 'Pimpinan'],
            ['parent_id' => null, 'is_active' => true],
        );

        $sekretaris = Position::updateOrCreate(
            ['name' => 'Sekretaris'],
            ['parent_id' => $pimpinan->id, 'is_active' => true],
        );

        $kabagUmum = Position::updateOrCreate(
            ['name' => 'Kepala Bagian Umum'],
            ['parent_id' => $sekretaris->id, 'is_active' => true],
        );

        $kabidData = Position::updateOrCreate(
            ['name' => 'Kepala Bidang Data dan Informasi'],
            ['parent_id' => $sekretaris->id, 'is_active' => true],
        );

        $stafUmum = Position::updateOrCreate(
            ['name' => 'Staf Umum'],
            ['parent_id' => $kabagUmum->id, 'is_active' => true],
        );

        $stafKearsipan = Position::updateOrCreate(
            ['name' => 'Staf Kearsipan'],
            ['parent_id' => $kabagUmum->id, 'is_active' => true],
        );

        return [
            'Pimpinan' => $pimpinan,
            'Sekretaris' => $sekretaris,
            'Kepala Bagian Umum' => $kabagUmum,
            'Kepala Bidang Data dan Informasi' => $kabidData,
            'Staf Umum' => $stafUmum,
            'Staf Kearsipan' => $stafKearsipan,
        ];
    }

    /**
     * Buat tindak lanjut bawaan.
     */
    private function seedFollowups(): void
    {
        foreach (['Segera', 'Tindak Lanjut', 'Selesai', 'Arsipkan'] as $name) {
            Followup::updateOrCreate(['name' => $name], ['is_active' => true]);
        }
    }

    /**
     * Buat klasifikasi arsip bawaan.
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
            RefKdKlasifikasi::updateOrCreate(['kode' => $kode], ['nama' => $nama, 'is_active' => true]);
        }
    }

    /**
     * Buat akun demo dengan peran dan jabatan aktif.
     *
     * @param  array{name: string, username: string, email: string}  $attributes
     */
    private function seedUser(string $role, array $attributes, Position $position): void
    {
        $user = User::updateOrCreate(
            ['email' => $attributes['email']],
            [
                'name' => $attributes['name'],
                'username' => $attributes['username'],
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ],
        );

        $user->syncRoles([$role]);

        $user->positions()->sync([
            $position->id => [
                'start_date' => now()->subYear()->toDateString(),
                'end_date' => null,
                'is_active' => true,
            ],
        ]);
    }
}

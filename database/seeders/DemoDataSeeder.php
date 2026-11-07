<?php

namespace Database\Seeders;

use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

/**
 * Data contoh untuk mencoba alur aplikasi: satu akun Pengelola dan dua akun
 * Pengguna di atas master data awal yang sama untuk semua perusahaan.
 *
 * Jalankan manual pada lingkungan lokal, bukan bagian dari DatabaseSeeder:
 * php artisan db:seed --class=DemoDataSeeder
 */
class DemoDataSeeder extends Seeder
{
    /**
     * Jalankan seeder.
     */
    public function run(): void
    {
        // Master data awal sama untuk semua perusahaan.
        $this->call(MasterDataSeeder::class);

        $positions = Position::query()
            ->whereIn('name', [
                'Kepala Bagian Umum',
                'Staf Umum',
                'Staf Kearsipan',
            ])
            ->get()
            ->keyBy('name');

        $this->seedUser('Pengelola', [
            'name' => 'Rina Pengelola',
            'username' => 'pengelola',
            'email' => 'pengelola@e-surat.test',
        ], $this->positionNamed($positions, 'Kepala Bagian Umum'));

        $this->seedUser('Pengguna', [
            'name' => 'Budi Staf Umum',
            'username' => 'pengguna1',
            'email' => 'pengguna1@e-surat.test',
        ], $this->positionNamed($positions, 'Staf Umum'));

        $this->seedUser('Pengguna', [
            'name' => 'Sari Staf Kearsipan',
            'username' => 'pengguna2',
            'email' => 'pengguna2@e-surat.test',
        ], $this->positionNamed($positions, 'Staf Kearsipan'));

        $this->command->info('Data contoh siap: 1 Pengelola, 2 Pengguna.');
    }

    /**
     * @param  Collection<string, Position>  $positions
     */
    private function positionNamed(Collection $positions, string $name): Position
    {
        $position = $positions->get($name);

        if (! $position instanceof Position) {
            $this->command->warn("Jabatan [{$name}] tidak tersedia pada master data; akun demo memakai jabatan lain.");

            return Position::query()->firstOrCreate(['name' => $name], ['is_active' => true]);
        }

        return $position;
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

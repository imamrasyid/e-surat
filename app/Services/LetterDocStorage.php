<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Penyimpanan lampiran surat pada disk privat default.
 *
 * Path selalu dihasilkan server (ULID + ekstensi tervalidasi), sehingga nama
 * file dari client tidak pernah menjadi bagian dari path dan tidak bisa
 * menimpa file lain. Nama asli disimpan terpisah pada kolom `name`.
 *
 * Semua operasi memakai `Storage::disk()` tanpa nama agar Laravel memakai disk
 * default environment; pada Laravel Cloud disk privat terpasang sebagai default.
 */
class LetterDocStorage
{
    public const DIRECTORY = 'letter-docs';

    /**
     * Nama disk default, untuk pesan/diagnostik.
     */
    public function diskName(): string
    {
        return (string) config('filesystems.default');
    }

    /**
     * Simpan file dan kembalikan storage path relatif terhadap disk.
     */
    public function store(UploadedFile $file): string
    {
        $extension = $file->extension() ?: 'pdf';
        $path = sprintf(
            '%s/%s/%s.%s',
            self::DIRECTORY,
            now()->format('Y/m'),
            Str::ulid(),
            $extension
        );

        $stored = Storage::disk()->putFileAs(dirname($path), $file, basename($path));

        if ($stored === false) {
            throw new RuntimeException("Gagal menyimpan lampiran ke disk [{$this->diskName()}].");
        }

        return $path;
    }

    public function exists(?string $path): bool
    {
        return filled($path) && Storage::disk()->exists((string) $path);
    }

    public function delete(?string $path): void
    {
        if (filled($path)) {
            Storage::disk()->delete((string) $path);
        }
    }

    /**
     * Alirkan file privat sebagai unduhan dengan nama asli dari database.
     */
    public function download(string $path, string $name): StreamedResponse
    {
        return Storage::disk()->download($path, $name);
    }

    /**
     * Alirkan file privat secara inline agar dirender browser pada dialog
     * pratinjau. Respons tetap privat dan tidak disimpan cache bersama.
     */
    public function preview(string $path, string $name): StreamedResponse
    {
        return Storage::disk()->response($path, $name, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}

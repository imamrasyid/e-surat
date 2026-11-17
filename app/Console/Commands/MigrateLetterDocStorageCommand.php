<?php

namespace App\Console\Commands;

use App\Models\LetterDoc;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Pindahkan file lampiran legacy dari disk `public` ke disk privat default.
 *
 * Database sudah menyimpan path hasil backfill migration; command ini
 * memastikan byte-nya benar-benar ada di object storage sebelum URL lama
 * berhenti dibaca. Idempotent: file yang sudah ada di disk target dilewati.
 */
class MigrateLetterDocStorageCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'letter-docs:migrate-storage
                            {--source-disk=public : Disk asal file legacy}
                            {--chunk=200 : Jumlah record per batch}';

    /**
     * @var string
     */
    protected $description = 'Salin lampiran surat legacy ke disk privat default';

    public function handle(): int
    {
        $targetDisk = (string) config('filesystems.default');
        $sourceDisk = $this->option('source-disk');
        $chunk = max(1, (int) $this->option('chunk'));

        if (! is_string($sourceDisk) || $sourceDisk === '') {
            $this->error('Opsi source-disk wajib berupa string.');

            return self::FAILURE;
        }

        if ($targetDisk === $sourceDisk) {
            $this->warn("Disk target dan sumber sama ({$targetDisk}); tidak ada yang perlu disalin.");

            return self::SUCCESS;
        }

        $copied = 0;
        $present = 0;
        $missing = 0;

        LetterDoc::query()
            ->whereNotNull('path')
            ->select(['id', 'path'])
            ->orderBy('id')
            ->chunkById($chunk, function ($docs) use ($sourceDisk, &$copied, &$present, &$missing): void {
                foreach ($docs as $doc) {
                    $path = (string) $doc->path;

                    if (Storage::disk()->exists($path)) {
                        $present++;

                        continue;
                    }

                    try {
                        if (! Storage::disk($sourceDisk)->exists($path)) {
                            $missing++;
                            $this->warn("File hilang untuk letter_docs#{$doc->id}: {$path}");

                            continue;
                        }

                        $stream = Storage::disk($sourceDisk)->readStream($path);

                        if ($stream === null) {
                            $missing++;
                            $this->warn("Gagal membaca letter_docs#{$doc->id}: {$path}");

                            continue;
                        }

                        if (! Storage::disk()->writeStream($path, $stream)) {
                            $missing++;
                            $this->warn("Gagal menulis letter_docs#{$doc->id}: {$path}");

                            continue;
                        }

                        $copied++;
                    } catch (Throwable $exception) {
                        $missing++;
                        $this->warn("Gagal menyalin letter_docs#{$doc->id}: ".$exception->getMessage());
                    }
                }
            });

        $this->info("Disk target {$targetDisk}: {$copied} disalin, {$present} sudah ada, {$missing} gagal/hilang.");

        return self::SUCCESS;
    }
}

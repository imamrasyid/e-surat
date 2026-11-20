<?php

namespace App\Actions\Letter;

use App\Events\InboxItemCreated;
use App\Models\Letter;
use App\Models\LetterDoc;
use App\Models\LetterRecipient;
use App\Models\Position;
use App\Models\RefAgendaSurat;
use App\Models\RefKdKlasifikasi;
use App\Models\User;
use App\Services\InboxCountsCache;
use App\Services\LetterActivityService;
use App\Services\LetterDocStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateComposeLetterAction
{
    public function __construct(private readonly LetterDocStorage $storage) {}

    /**
     * Create and store a new compose letter along with its agenda, recipients, and attachments.
     *
     * @param  array<string, mixed>  $validated
     * @param  array<int, UploadedFile>  $lampiranFiles
     */
    public function handle(
        User $user,
        Position $currentPosition,
        array $validated,
        array $lampiranFiles = []
    ): Letter {
        return DB::transaction(function () use ($user, $currentPosition, $validated, $lampiranFiles): Letter {
            $customUlid = $this->customUlid();

            // MEMBUAT NOMOR AGENDA
            // Baris klasifikasi dikunci dulu: dua operator yang menyimpan
            // surat berklasifikasi sama bersamaan membaca nomor urut secara
            // berurutan, bukan membaca nilai yang sama lalu gagal di index unik.
            // SQLite (unit test) mengabaikan lock; MySQL memakai FOR UPDATE.
            $klasifikasi = RefKdKlasifikasi::query()
                ->where('kode', $validated['agenda'])
                ->lockForUpdate()
                ->firstOrFail();

            $nomorUrutTerakhir = RefAgendaSurat::query()
                ->where('klasifikasi_id', $klasifikasi->id)
                ->where('tahun', $validated['agendaTahun'])
                ->max('nomor_urut');

            $nomorUrutBaru = $nomorUrutTerakhir ? $nomorUrutTerakhir + 1 : 1;
            $nomorAgenda = "{$nomorUrutBaru}/{$klasifikasi->kode}/{$validated['agendaTahun']}";
            $agenda = RefAgendaSurat::create([
                'klasifikasi_id' => $klasifikasi->id,
                'nomor_urut' => $nomorUrutBaru,
                'tahun' => $validated['agendaTahun'],
                'nomor_agenda' => $nomorAgenda,
            ]);

            // INSERT SURAT
            $letter = Letter::create([
                'letter_id' => $customUlid,
                'sender' => $validated['pengirim'],
                'number' => $validated['nomor'],
                'date' => $validated['tglSuratStr'],
                'receipt_date' => $validated['tglDiterimaStr'],
                'subject' => $validated['perihal'],
                'category' => $validated['kategori'],
                // Pemanggil Action lama belum mengirim metadata TNDE; nilai
                // default menjaga surat lama tetap tercatat sebagai biasa.
                'security_classification' => $validated['security_classification'] ?? 'B',
                'received_via' => $validated['received_via'] ?? 'other',
                'agenda' => $nomorAgenda,
                'created_by' => $user->id,
            ]);

            // Audit selalu tercatat, termasuk pemanggil programatik tanpa sesi.
            LetterActivityService::log($letter->id, 'register_incoming', 'Mendaftarkan surat masuk', [
                'category' => $validated['kategori'],
                'security_classification' => $validated['security_classification'] ?? 'B',
                'received_via' => $validated['received_via'] ?? 'other',
            ], $user->id);

            // INSERT PENERIMA
            foreach ($validated['penerima'] as $penerima) {
                $recipient = LetterRecipient::create([
                    'letter_id' => $letter->id,
                    'from_user_id' => $user->id,
                    'from_position_id' => $currentPosition->id,
                    'to_position_id' => $penerima['id'],
                    'instruction' => $validated['catatan'] ?? null,
                    'received_at' => now(),
                ]);

                InboxItemCreated::dispatch($recipient);
            }

            // Hapus cache badge inbox penerima surat
            InboxCountsCache::forgetPositions(array_column($validated['penerima'], 'id'));

            // INSERT LAMPIRAN
            foreach ($lampiranFiles as $lampiran) {
                LetterDoc::create([
                    'agenda_id' => $agenda->id,
                    'letter_id' => $letter->id,
                    'name' => $lampiran->getClientOriginalName(),
                    'path' => $this->storage->store($lampiran),
                ]);
            }

            return $letter;
        });
    }

    private function customUlid(): string
    {
        $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $base58Length = 26;

        // Ambil ULID asli
        $ulid = Str::ulid()->toBase32();

        // Ambil 10 karakter pertama sebagai timestamp ULID (Base32)
        $timestampBase32 = substr($ulid, 0, 10);

        // Decode Base32 -> integer
        $base32Alphabet = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
        $value = 0;
        foreach (str_split($timestampBase32) as $char) {
            $value = $value * 32 + strpos($base32Alphabet, $char);
        }

        // Encode integer -> Base58
        $timestampBase58 = '';
        $maxIndex = strlen($alphabet) - 1;
        while ($value > 0) {
            $timestampBase58 = $alphabet[$value % 58].$timestampBase58;
            $value = intdiv($value, 58);
        }

        // Pastikan panjang konsisten (misal 8 karakter), pad jika perlu
        $timestampBase58 = str_pad($timestampBase58, 8, $alphabet[0], STR_PAD_LEFT);

        // Tambah "salt" random kecil (2 karakter) agar timestamp lebih unik
        $salt = '';
        for ($i = 0; $i < 2; $i++) {
            $salt .= $alphabet[random_int(0, $maxIndex)];
        }

        // Sisa karakter random untuk mencapai total panjang
        $remainingLength = $base58Length - strlen($timestampBase58) - strlen($salt);
        $randomPart = '';
        for ($i = 0; $i < $remainingLength; $i++) {
            $randomPart .= $alphabet[random_int(0, $maxIndex)];
        }

        return $timestampBase58.$salt.$randomPart;
    }
}

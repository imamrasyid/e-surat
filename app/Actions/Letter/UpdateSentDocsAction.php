<?php

namespace App\Actions\Letter;

use App\Models\Letter;
use App\Models\LetterDoc;
use App\Models\RefAgendaSurat;
use App\Services\LetterActivityService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UpdateSentDocsAction
{
    /**
     * Update sent letter attachments (delete marked docs, upload new docs, and log activity).
     *
     * @param  array<int, int|string>  $hapusIds
     * @param  array<int, UploadedFile>  $lampiranFiles
     */
    public function handle(
        Letter $letter,
        array $hapusIds = [],
        array $lampiranFiles = []
    ): void {
        DB::transaction(function () use ($letter, $hapusIds, $lampiranFiles): void {
            // Hapus lampiran (set is_active = false dan hapus file dari storage)
            $docsToDelete = collect();
            if (! empty($hapusIds)) {
                $docsToDelete = LetterDoc::where('letter_id', $letter->id)
                    ->whereIn('id', $hapusIds)
                    ->where('is_active', true)
                    ->get();

                foreach ($docsToDelete as $doc) {
                    $storagePath = str_replace('/storage/', '', $doc->url);
                    if (Storage::disk('public')->exists($storagePath)) {
                        Storage::disk('public')->delete($storagePath);
                    }

                    $doc->update(['is_active' => false]);
                }
            }

            // Tambah lampiran baru
            if (! empty($lampiranFiles)) {
                $agenda = RefAgendaSurat::where('nomor_agenda', $letter->agenda)->firstOrFail();
                $klasifikasi = $agenda->klasifikasi;

                $receiptDate = date('Y-m-d', strtotime($letter->receipt_date));

                foreach ($lampiranFiles as $lampiran) {
                    $path = 'attachment/'.$receiptDate;
                    $filename = $klasifikasi->kode.$agenda->nomor_urut.'_'.$lampiran->getClientOriginalName();
                    Storage::disk('public')->putFileAs($path, $lampiran, $filename);

                    $storeUrl = "/storage/{$path}/{$filename}";
                    LetterDoc::create([
                        'agenda_id' => $agenda->id,
                        'letter_id' => $letter->id,
                        'name' => $filename,
                        'url' => $storeUrl,
                    ]);
                }
            }

            // Log aktivitas perubahan lampiran
            $changes = [];
            if ($docsToDelete->isNotEmpty()) {
                $changes['removed'] = $docsToDelete->pluck('name')->toArray();
            }
            if (! empty($lampiranFiles)) {
                $changes['added'] = collect($lampiranFiles)
                    ->map(fn (UploadedFile $f) => $f->getClientOriginalName())
                    ->toArray();
            }

            if (! empty($changes)) {
                LetterActivityService::log(
                    $letter->id,
                    'update_docs',
                    'Memperbarui lampiran surat',
                    $changes
                );
            }
        });
    }
}

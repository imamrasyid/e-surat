<?php

namespace App\Actions\Disposition;

use App\Models\Disposition;
use App\Models\LetterDoc;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CreateDispositionAction
{
    /**
     * Create disposition recipients, cc recipients, and upload attachments.
     *
     * @param  array<string, mixed>  $validated
     * @param  array<int, UploadedFile>  $lampiranFiles
     */
    public function handle(
        User $user,
        Position $currentPosition,
        array $validated,
        array $lampiranFiles = []
    ): void {
        $followups = null;
        if (! empty($validated['followup'])) {
            $followups = implode('; ', array_column($validated['followup'], 'name'));
        }

        DB::transaction(function () use ($validated, $user, $currentPosition, $followups, $lampiranFiles): void {
            // INSERT PENERIMA
            foreach ($validated['penerima'] as $penerima) {
                Disposition::create([
                    'letter_id' => $validated['letter_id_primary'],
                    'letter_recipient_id' => $validated['letter_recipient_id'],
                    'from_user_id' => $user->id,
                    'from_position_id' => $currentPosition->id,
                    'to_position_id' => $penerima['id'],
                    'instruction' => $validated['catatan'] ?? null,
                    'followup' => $followups,
                    'received_at' => now(),
                ]);
            }

            if (! empty($validated['penerima_cc'])) {
                foreach ($validated['penerima_cc'] as $penerima) {
                    Disposition::create([
                        'letter_id' => $validated['letter_id_primary'],
                        'letter_recipient_id' => $validated['letter_recipient_id'],
                        'is_cc' => 1,
                        'from_user_id' => $user->id,
                        'from_position_id' => $currentPosition->id,
                        'to_position_id' => $penerima['id'],
                        'received_at' => now(),
                    ]);
                }
            }

            // INSERT LAMPIRAN
            foreach ($lampiranFiles as $lampiran) {
                $path = 'attachment/'.$validated['letter_receipt_date'];
                $filename = 'DSP'.now()->format('His').'_'.$lampiran->getClientOriginalName();
                Storage::disk('public')->putFileAs($path, $lampiran, $filename);

                $storeUrl = "/storage/{$path}/{$filename}";
                LetterDoc::create([
                    'letter_id' => $validated['letter_id_primary'],
                    'name' => $filename,
                    'url' => $storeUrl,
                ]);
            }
        });
    }
}

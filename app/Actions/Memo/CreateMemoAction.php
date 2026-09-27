<?php

namespace App\Actions\Memo;

use App\Models\LetterDoc;
use App\Models\Memo;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CreateMemoAction
{
    /**
     * Create memo recipients, cc recipients, and upload attachments.
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
        DB::transaction(function () use ($validated, $user, $currentPosition, $lampiranFiles): void {
            // INSERT PENERIMA
            foreach ($validated['penerima'] as $penerima) {
                Memo::create([
                    'letter_id' => $validated['letter_id_primary'],
                    'letter_recipient_id' => $validated['letter_recipient_id'],
                    'from_user_id' => $user->id,
                    'from_position_id' => $currentPosition->id,
                    'to_position_id' => $penerima['id'],
                    'instruction' => $validated['catatan'] ?? null,
                    'received_at' => now(),
                ]);
            }

            if (! empty($validated['penerima_cc'])) {
                foreach ($validated['penerima_cc'] as $penerima) {
                    Memo::create([
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
                $filename = 'MMO'.now()->format('His').'_'.$lampiran->getClientOriginalName();
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

<?php

namespace App\Actions\Memo;

use App\Events\InboxItemCreated;
use App\Models\LetterDoc;
use App\Models\Memo;
use App\Models\Position;
use App\Models\User;
use App\Services\InboxCountsCache;
use App\Services\LetterDocStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CreateMemoAction
{
    public function __construct(private readonly LetterDocStorage $storage) {}

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
                $memo = Memo::create([
                    'letter_id' => $validated['letter_id_primary'],
                    'letter_recipient_id' => $validated['letter_recipient_id'],
                    'from_user_id' => $user->id,
                    'from_position_id' => $currentPosition->id,
                    'to_position_id' => $penerima['id'],
                    'instruction' => $validated['catatan'] ?? null,
                    'received_at' => now(),
                ]);

                InboxItemCreated::dispatch($memo);
            }

            if (! empty($validated['penerima_cc'])) {
                foreach ($validated['penerima_cc'] as $penerima) {
                    $ccMemo = Memo::create([
                        'letter_id' => $validated['letter_id_primary'],
                        'letter_recipient_id' => $validated['letter_recipient_id'],
                        'is_cc' => 1,
                        'from_user_id' => $user->id,
                        'from_position_id' => $currentPosition->id,
                        'to_position_id' => $penerima['id'],
                        'received_at' => now(),
                    ]);

                    InboxItemCreated::dispatch($ccMemo);
                }
            }

            // Hapus cache badge inbox untuk posisi penerima & cc
            InboxCountsCache::forgetPositions(
                array_merge(
                    array_column($validated['penerima'], 'id'),
                    array_column($validated['penerima_cc'] ?? [], 'id')
                )
            );

            // INSERT LAMPIRAN
            foreach ($lampiranFiles as $lampiran) {
                LetterDoc::create([
                    'letter_id' => $validated['letter_id_primary'],
                    'name' => $lampiran->getClientOriginalName(),
                    'path' => $this->storage->store($lampiran),
                ]);
            }
        });
    }
}

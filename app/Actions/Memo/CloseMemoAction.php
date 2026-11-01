<?php

namespace App\Actions\Memo;

use App\Events\InboxItemCreated;
use App\Models\Letter;
use App\Models\Memo;
use App\Models\Position;
use App\Models\User;
use App\Services\InboxCountsCache;
use Illuminate\Support\Facades\DB;

class CloseMemoAction
{
    /**
     * Create closing memo and update letter status to close.
     *
     * @param  array<string, mixed>  $validated
     */
    public function handle(
        User $user,
        Position $currentPosition,
        array $validated
    ): Memo {
        return DB::transaction(function () use ($validated, $user, $currentPosition): Memo {
            // INSERT MEMO
            $memo = Memo::create([
                'letter_id' => $validated['letter_id_primary'],
                'letter_recipient_id' => $validated['letter_recipient_id'],
                'from_user_id' => $user->id,
                'from_position_id' => $currentPosition->id,
                'to_position_id' => $validated['to_position_id'],
                'instruction' => $validated['catatan'] ?? null,
                'received_at' => now(),
            ]);

            // UPDATE LETTER STATUS
            $letter = Letter::where('id', $validated['letter_id_primary'])->firstOrFail();
            $letter->update([
                'status' => 'close',
            ]);

            // Nota dinas penutupan tetap dihitung sebagai nota dinas baru.
            InboxItemCreated::dispatch($memo);

            // Hapus cache badge inbox posisi penerima memo penutupan
            InboxCountsCache::forgetPositions([$validated['to_position_id']]);

            return $memo;
        });
    }
}

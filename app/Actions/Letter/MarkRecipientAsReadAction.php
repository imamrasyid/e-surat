<?php

namespace App\Actions\Letter;

use App\Models\LetterRecipient;
use App\Services\InboxCountsCache;
use App\Services\LetterActivityService;

class MarkRecipientAsReadAction
{
    /**
     * Mark a letter recipient record as read by setting the read_at timestamp.
     */
    public function handle(LetterRecipient $recipient): bool
    {
        $wasUnread = $recipient->read_at === null;

        $updated = $recipient->update(['read_at' => now()]);

        // Hapus cache badge inbox posisi penerima (primary/undangan)
        InboxCountsCache::forgetPositions([$recipient->to_position_id]);

        // Hanya transisi pertama null -> read_at yang dicatat sebagai riwayat.
        if ($updated && $wasUnread) {
            LetterActivityService::log($recipient->letter_id, 'read_inbox', 'Membaca surat masuk', [
                'recipient_id' => $recipient->id,
            ]);
        }

        return $updated;
    }
}

<?php

namespace App\Actions\Letter;

use App\Models\LetterRecipient;
use App\Services\InboxCountsCache;

class MarkRecipientAsReadAction
{
    /**
     * Mark a letter recipient record as read by setting the read_at timestamp.
     */
    public function handle(LetterRecipient $recipient): bool
    {
        $updated = $recipient->update(['read_at' => now()]);

        // Hapus cache badge inbox posisi penerima (primary/undangan)
        InboxCountsCache::forgetPositions([$recipient->to_position_id]);

        return $updated;
    }
}

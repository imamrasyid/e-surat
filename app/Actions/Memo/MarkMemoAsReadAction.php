<?php

namespace App\Actions\Memo;

use App\Models\Memo;
use App\Services\InboxCountsCache;

class MarkMemoAsReadAction
{
    /**
     * Mark a memo as read by setting the read_at timestamp.
     */
    public function handle(Memo $memo): bool
    {
        $updated = $memo->update(['read_at' => now()]);

        // Hapus cache badge inbox posisi penerima memo
        InboxCountsCache::forgetPositions([$memo->to_position_id]);

        return $updated;
    }
}

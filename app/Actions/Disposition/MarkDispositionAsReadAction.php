<?php

namespace App\Actions\Disposition;

use App\Models\Disposition;
use App\Services\InboxCountsCache;

class MarkDispositionAsReadAction
{
    /**
     * Mark a disposition as read by setting the read_at timestamp.
     */
    public function handle(Disposition $disposition): bool
    {
        $updated = $disposition->update(['read_at' => now()]);

        // Hapus cache badge inbox posisi penerima disposisi
        InboxCountsCache::forgetPositions([$disposition->to_position_id]);

        return $updated;
    }
}

<?php

namespace App\Actions\Disposition;

use App\Models\Disposition;

class MarkDispositionAsReadAction
{
    /**
     * Mark a disposition as read by setting the read_at timestamp.
     */
    public function handle(Disposition $disposition): bool
    {
        return $disposition->update(['read_at' => now()]);
    }
}

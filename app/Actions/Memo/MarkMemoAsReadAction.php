<?php

namespace App\Actions\Memo;

use App\Models\Memo;

class MarkMemoAsReadAction
{
    /**
     * Mark a memo as read by setting the read_at timestamp.
     */
    public function handle(Memo $memo): bool
    {
        return $memo->update(['read_at' => now()]);
    }
}

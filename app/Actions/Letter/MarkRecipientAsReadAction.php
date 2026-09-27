<?php

namespace App\Actions\Letter;

use App\Models\LetterRecipient;

class MarkRecipientAsReadAction
{
    /**
     * Mark a letter recipient record as read by setting the read_at timestamp.
     */
    public function handle(LetterRecipient $recipient): bool
    {
        return $recipient->update(['read_at' => now()]);
    }
}

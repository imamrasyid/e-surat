<?php

namespace App\Actions\Letter;

use App\Models\LetterRecipient;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AuthorizeFollowUpAction
{
    /**
     * Disposisi/nota dinas hanya boleh dibuat dari surat yang benar-benar
     * sampai di jabatan aktif user: penerima surat asal, atau penerima
     * disposisi/nota dinas (bukan tembusan) dalam rantai surat yang sama.
     */
    public function handle(?User $user, mixed $recipientId, mixed $letterId): bool
    {
        $currentPosition = $user?->currentPosition()->first();

        if ($currentPosition === null || ! is_scalar($recipientId) || ! is_scalar($letterId)) {
            return false;
        }

        return LetterRecipient::query()
            ->whereKey($recipientId)
            ->where('letter_id', $letterId)
            ->where(function (Builder $query) use ($currentPosition): void {
                $query->where('to_position_id', $currentPosition->id)
                    ->orWhereHas('dispositions', fn (Builder $query) => $query
                        ->where('to_position_id', $currentPosition->id)
                        ->where('is_cc', false))
                    ->orWhereHas('memos', fn (Builder $query) => $query
                        ->where('to_position_id', $currentPosition->id)
                        ->where('is_cc', false));
            })
            ->exists();
    }
}

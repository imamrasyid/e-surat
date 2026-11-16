<?php

namespace App\Http\Controllers;

use App\Actions\Letter\GetLetterTimelineAction;
use App\Models\Disposition;
use App\Models\Letter;
use App\Models\LetterRecipient;
use App\Models\Memo;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

abstract class Controller
{
    /**
     * @return Collection<int, LetterRecipient|Disposition|Memo>
     */
    public static function getTimeline(string|int $letter_id): Collection
    {
        return app(GetLetterTimelineAction::class)->handle((string) $letter_id);
    }

    /**
     * Penciptaan disposisi/nota dinas hanya untuk surat yang benar-benar
     * sampai di jabatan aktif user: penerima harus milik surat itu dan
     * ditujukan ke jabatan aktifnya.
     */
    protected function authorizeFollowUp(Request $request, string|int $recipientId, Letter $letter): void
    {
        $currentPosition = $request->user()?->currentPosition()->first();

        $authorized = $currentPosition !== null
            && LetterRecipient::query()
                ->whereKey($recipientId)
                ->where('letter_id', $letter->id)
                ->where('to_position_id', $currentPosition->id)
                ->exists();

        abort_unless($authorized, 403, 'Anda tidak memiliki akses ke surat ini.');
    }
}

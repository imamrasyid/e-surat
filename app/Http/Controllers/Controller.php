<?php

namespace App\Http\Controllers;

use App\Actions\Letter\AuthorizeFollowUpAction;
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
     * sampai di jabatan aktif user: penerima surat asal, atau penerima
     * disposisi/nota dinas (bukan tembusan) dalam rantai surat yang sama.
     */
    protected function authorizeFollowUp(Request $request, string|int $recipientId, Letter $letter): void
    {
        abort_unless(
            app(AuthorizeFollowUpAction::class)->handle($request->user(), $recipientId, $letter->id),
            403,
            'Anda tidak memiliki akses ke surat ini.'
        );
    }
}

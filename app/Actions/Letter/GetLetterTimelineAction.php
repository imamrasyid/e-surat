<?php

namespace App\Actions\Letter;

use App\Models\Disposition;
use App\Models\LetterRecipient;
use App\Models\Memo;
use Illuminate\Support\Collection;

class GetLetterTimelineAction
{
    /**
     * Get unified timeline of recipients, dispositions, and memos for a letter.
     *
     * @return Collection<int, LetterRecipient|Disposition|Memo>
     */
    public function handle(string $letterId): Collection
    {
        $recipients = LetterRecipient::where('letter_id', $letterId)
            ->select(
                'letter_id',
                'from_position_id',
                'to_position_id',
                'instruction',
                'followup',
                'received_at',
                'read_at',
            )
            ->selectRaw('0 as is_cc')
            ->selectRaw('"recipient" as type')
            ->with(['fromPosition:id,name', 'toPosition:id,name'])
            ->get();

        $dispositions = Disposition::where('letter_id', $letterId)
            ->select(
                'letter_id',
                'is_cc',
                'from_position_id',
                'to_position_id',
                'instruction',
                'followup',
                'received_at',
                'read_at',
            )
            ->selectRaw('"disposition" as type')
            ->with(['fromPosition:id,name', 'toPosition:id,name'])
            ->get();

        $memos = Memo::where('letter_id', $letterId)
            ->select(
                'letter_id',
                'is_cc',
                'from_position_id',
                'to_position_id',
                'instruction',
                'followup',
                'received_at',
                'read_at',
            )
            ->selectRaw('"memo" as type')
            ->with(['fromPosition:id,name', 'toPosition:id,name'])
            ->get();

        return $recipients
            ->concat($dispositions)
            ->concat($memos)
            ->sortBy('received_at')
            ->values();
    }
}

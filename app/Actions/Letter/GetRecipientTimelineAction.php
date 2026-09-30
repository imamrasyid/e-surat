<?php

namespace App\Actions\Letter;

use App\Models\Disposition;
use App\Models\Memo;
use Illuminate\Support\Collection;

class GetRecipientTimelineAction
{
    /**
     * Get the combined disposition and memo timeline for a letter recipient.
     *
     * @return Collection<int, Disposition|Memo>
     */
    public function handle(string $recipientId): Collection
    {
        $columns = [
            'is_cc',
            'from_position_id',
            'to_position_id',
            'instruction',
            'followup',
            'received_at',
            'read_at',
        ];

        $dispositions = Disposition::query()
            ->where('letter_recipient_id', $recipientId)
            ->select($columns)
            ->selectRaw("'disposition' as type")
            ->with(['fromPosition:id,name', 'toPosition:id,name'])
            ->get();

        $memos = Memo::query()
            ->where('letter_recipient_id', $recipientId)
            ->select($columns)
            ->selectRaw("'memo' as type")
            ->with(['fromPosition:id,name', 'toPosition:id,name'])
            ->get();

        return $dispositions
            ->concat($memos)
            ->sortBy('received_at')
            ->values();
    }
}

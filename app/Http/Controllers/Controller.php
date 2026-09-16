<?php

namespace App\Http\Controllers;

use App\Models\Disposition;
use App\Models\LetterRecipient;
use App\Models\Memo;
use Illuminate\Support\Facades\DB;

abstract class Controller
{
    public static function getTimeline($letter_id)
    {
        $recipients = LetterRecipient::where('letter_id', $letter_id)
            ->select(
                'letter_id',
                DB::raw('0 as is_cc'),
                'from_position_id',
                'to_position_id',
                'instruction',
                'followup',
                'received_at',
                'read_at',
                DB::raw("'recipient' as type"),
            )
            ->with(['fromPosition:id,name', 'toPosition:id,name'])
            ->get();

        $dispositions = Disposition::where('letter_id', $letter_id)
            ->select(
                'letter_id',
                'is_cc',
                'from_position_id',
                'to_position_id',
                'instruction',
                'followup',
                'received_at',
                'read_at',
                DB::raw("'disposition' as type"),
            )
            ->with(['fromPosition:id,name', 'toPosition:id,name'])
            ->get();

        $memos = Memo::where('letter_id', $letter_id)
            ->select(
                'letter_id',
                'is_cc',
                'from_position_id',
                'to_position_id',
                'instruction',
                'followup',
                'received_at',
                'read_at',
                DB::raw("'memo' as type"),
            )
            ->with(['fromPosition:id,name', 'toPosition:id,name'])
            ->get();

        return collect([$recipients, $dispositions, $memos])
            ->collapse()
            ->sortBy('received_at')
            ->values();
    }
}

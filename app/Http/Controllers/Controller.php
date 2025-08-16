<?php

namespace App\Http\Controllers;

use App\Models\Disposition;
use App\Models\LetterRecipient;
use Illuminate\Support\Facades\DB;

abstract class Controller
{
    public static function getTimeline($letter_id)
    {
        $recipients = LetterRecipient::where('letter_id', $letter_id)
            ->select(
                'letter_id',
                DB::raw("0 as is_cc"),
                'from_position_id',
                'to_position_id',
                'instruction',
                'followup',
                'received_at',
                'read_at',
            )
            ->with(['fromPosition:id,name', 'toPosition:id,name']);

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
            )
            ->with(['fromPosition:id,name', 'toPosition:id,name']);

        return $recipients->union($dispositions)
            ->orderBy('received_at', 'asc')
            ->get();
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Disposition;
use App\Models\Memo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    public function recipientTimeline(string $recipient): JsonResponse
    {
        $dispositions = Disposition::where('letter_recipient_id', $recipient)
            ->select(
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

        $memos = Memo::where('letter_recipient_id', $recipient)
            ->select(
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

        $data = collect([$dispositions, $memos])
            ->collapse()
            ->sortBy('received_at')
            ->values();

        return response()->json($data);
    }
}

<?php

namespace App\Http\Controllers;

use App\Actions\Letter\GetRecipientTimelineAction;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    /**
     * Get the combined disposition and memo timeline for a letter recipient.
     */
    public function recipientTimeline(string $recipient, GetRecipientTimelineAction $action): JsonResponse
    {
        return response()->json($action->handle($recipient));
    }
}

<?php

namespace App\Http\Controllers;

use App\Actions\Letter\GetRecipientTimelineAction;
use App\Models\LetterRecipient;
use App\Services\LetterAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    /**
     * Get the combined disposition and memo timeline for a letter recipient.
     */
    public function recipientTimeline(
        Request $request,
        LetterRecipient $recipient,
        LetterAccess $access,
        GetRecipientTimelineAction $action
    ): JsonResponse {
        $user = $request->user();

        abort_if($user === null, 403);

        $letter = $recipient->letter;

        abort_if($letter === null, 404);
        abort_unless($access->allows($user, $letter), 403);

        return response()->json($action->handle((string) $recipient->id));
    }
}

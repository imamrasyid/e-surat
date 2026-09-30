<?php

namespace App\Http\Controllers;

use App\Actions\Letter\GetLetterTimelineAction;
use App\Models\Disposition;
use App\Models\LetterRecipient;
use App\Models\Memo;
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
}

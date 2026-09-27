<?php

namespace App\Http\Controllers;

use App\Actions\Letter\GetLetterTimelineAction;
use Illuminate\Support\Collection;

abstract class Controller
{
    public static function getTimeline(string $letter_id): Collection
    {
        return app(GetLetterTimelineAction::class)->handle($letter_id);
    }
}

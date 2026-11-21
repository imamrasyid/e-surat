<?php

namespace App\Http\Controllers\Inbox;

use App\Actions\Letter\MarkRecipientAsReadAction;
use App\Actions\Notification\SyncNotificationReadStateAction;
use App\Http\Controllers\Controller;
use App\Models\LetterRecipient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class PrimaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $currentPosition = $request->user()?->currentPosition()?->first();

        // Kalau user tidak punya currentPosition
        if (! $currentPosition) {
            return Inertia::render('app/primary/Index', [
                'breadcrumbItems' => [
                    [
                        'title' => 'Utama',
                        'route' => 'inbox.primary.index',
                    ],
                ],
                'primary' => fn () => new LengthAwarePaginator([], 0, 15),
            ]);
        }

        $primary = LetterRecipient::query()
            ->select('id', 'letter_id', 'received_at', 'read_at')
            ->where('to_position_id', $currentPosition->id)
            ->with(['letter' => function ($query) {
                $query->select('id', 'letter_id', 'agenda', 'sender', 'number', 'subject', 'security_classification', 'received_via');
            }])
            ->with(['activityDispo', 'activityMemo'])
            ->whereHas('letter', function (Builder $query) {
                $query->where('type', 'in')
                    ->where('category', '1');
            })
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $search = $request->input('search');
                $query->whereHas('letter', function (Builder $subQuery) use ($search) {
                    $subQuery->where(function (Builder $sq) use ($search) {
                        $sq->whereLike('agenda', '%'.$search.'%')
                            ->orWhereLike('sender', '%'.$search.'%')
                            ->orWhereLike('subject', '%'.$search.'%')
                            ->orWhereLike('number', '%'.$search.'%');
                    });
                });
            })
            ->when($request->filled('filter'), function (Builder $query) use ($request) {
                match ($request->input('filter')) {
                    'read' => $query->whereNotNull('read_at'),
                    'unread' => $query->whereNull('read_at'),
                    default => null,
                };
            })
            ->orderBy('received_at', 'desc')
            ->paginate(15)
            ->through(fn (LetterRecipient $recipient): LetterRecipient => $recipient
                ->append('activities')
                ->makeHidden(['activityDispo', 'activityMemo']))
            ->withQueryString();

        return Inertia::render('app/primary/Index', [
            'breadcrumbItems' => [
                [
                    'title' => 'Utama',
                    'route' => 'inbox.primary.index',
                ],
            ],
            'primary' => fn () => $primary,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(
        Request $request,
        LetterRecipient $recipient,
        string $letter_id,
        MarkRecipientAsReadAction $markAsRead,
        SyncNotificationReadStateAction $sync
    ): Response|RedirectResponse {
        $currentPosition = $request->user()?->currentPosition()->first();

        if ($recipient->to_position_id !== $currentPosition?->id) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Anda tidak memiliki akses ke surat ini.',
            ]);

            return back();
        }

        if ($recipient->letter()->where('letter_id', $letter_id)->doesntExist()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Surat tidak ditemukan atau tidak sesuai dengan penerima surat.',
            ]);

            return back();
        }

        if ($request->isMethod('patch')) {
            try {
                $markAsRead->handle($recipient);
                $sync->markForItem(
                    $request->user(),
                    'primary',
                    $recipient->id,
                    route('inbox.primary.show', [
                        'recipient' => $recipient->id,
                        'letter_id' => $letter_id,
                    ]),
                );
            } catch (Throwable $th) {
                Inertia::flash('toast', [
                    'type' => 'error',
                    'message' => 'Gagal memperbarui status baca: '.$th->getMessage(),
                ]);

                return back();
            }
        }

        $letter = $recipient->letter()->with([
            'senderUser:id,name',
            'recipients:id,letter_id,from_position_id,to_position_id,instruction,received_at,read_at',
            'docs:id,letter_id,name',
            'activities.user:id,name',
        ])->firstOrFail();

        return Inertia::render('app/primary/Show', [
            'breadcrumbItems' => [
                [
                    'title' => 'Utama',
                    'route' => 'inbox.primary.index',
                ],
                [
                    'title' => 'Detail Surat',
                ],
            ],
            'letter' => fn () => $letter,
            'recipient' => fn () => $recipient->id,
        ]);
    }
}

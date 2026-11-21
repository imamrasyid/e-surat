<?php

namespace App\Http\Controllers;

use App\Actions\Memo\CloseMemoAction;
use App\Actions\Memo\CreateMemoAction;
use App\Actions\Memo\MarkMemoAsReadAction;
use App\Actions\Notification\SyncNotificationReadStateAction;
use App\Http\Requests\StoreMemoCloseRequest;
use App\Http\Requests\StoreMemoRequest;
use App\Models\Letter;
use App\Models\Memo;
use App\Models\Position;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class MemoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $currentPosition = $user?->currentPosition()?->first();

        // Kalau user tidak punya currentPosition
        if (! $currentPosition) {
            return Inertia::render('app/memo/Index', [
                'breadcrumbItems' => [
                    [
                        'title' => 'Nota Dinas',
                        'route' => 'memo.index',
                    ],
                ],
                'memo' => fn () => new LengthAwarePaginator([], 0, 15),
            ]);
        }

        $memo = Memo::query()
            ->where('to_position_id', $currentPosition->id)
            ->with([
                'fromPosition:id,name',
                'letter:id,letter_id,agenda,sender,number,subject',
            ])
            ->when($request->input('search'), function (Builder $query, $search) {
                $query->whereHas('letter', function (Builder $query) use ($search) {
                    $query->where('agenda', 'like', '%'.$search.'%');
                });
            })
            ->when($request->input('filter'), function (Builder $query, $filter) {
                match ($filter) {
                    'read' => $query->whereNotNull('read_at'),
                    'unread' => $query->whereNull('read_at'),
                    default => null,
                };
            })
            ->orderBy('received_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('app/memo/Index', [
            'breadcrumbItems' => [
                [
                    'title' => 'Nota Dinas',
                    'route' => 'memo.index',
                ],
            ],
            'memo' => fn () => $memo,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, string $recipient, string $key): Response
    {
        $keyDecode = base64_decode($key);
        $letter_id = substr($keyDecode, -26);

        $letter = Letter::where('letter_id', $letter_id)->where('status', 'open')->firstOrFail();

        $this->authorizeFollowUp($request, $recipient, $letter);

        $recipients = $this->getTimeline($letter->id);

        $positions = Position::query()
            ->select('id', 'name')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('app/memo/Create', [
            'breadcrumbItems' => [
                [
                    'title' => 'Nota Dinas',
                    'route' => 'memo.index',
                ],
                [
                    'title' => 'Tambah Baru',
                ],
            ],
            'letter' => fn () => $letter,
            'recipients' => fn () => $recipients,
            'recipient_id' => fn () => $recipient,
            'positions' => fn () => $positions,
        ]);
    }

    public function createClose(Memo $memo, string $letter_id): Response
    {
        $letter = $memo->letter()->where('status', 'open')->firstOrFail();

        $recipients = $this->getTimeline($letter->id);

        return Inertia::render('app/memo/CreateClose', [
            'breadcrumbItems' => [
                [
                    'title' => 'Nota Dinas',
                    'route' => 'memo.index',
                ],
                [
                    'title' => 'Penutupan Surat',
                ],
            ],
            'letter' => fn () => $letter,
            'recipients' => fn () => $recipients,
            'memo' => fn () => $memo,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMemoRequest $request, CreateMemoAction $action): RedirectResponse
    {
        $user = $request->user();
        $currentPosition = $user->currentPosition()->first();
        $validated = $request->validated();

        try {
            $action->handle(
                $user,
                $currentPosition,
                $validated,
                $request->file('lampiran', [])
            );

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Nota dinas berhasil dikirim.',
            ]);

            $routes = [
                'primary' => [
                    'route' => 'inbox.primary.show',
                    'params' => fn ($req) => [
                        'recipient' => $req->letter_recipient_id,
                        'letter_id' => $req->letter_id,
                    ],
                ],
                'undangan' => [
                    'route' => 'inbox.undangan.show',
                    'params' => fn ($req) => [
                        'recipient' => $req->letter_recipient_id,
                        'letter_id' => $req->letter_id,
                    ],
                ],
                'disposisi' => [
                    'route' => 'dispo.index',
                    'params' => fn () => [],
                ],
                'memo' => [
                    'route' => 'memo.index',
                    'params' => fn () => [],
                ],
            ];

            if (isset($routes[$validated['type']])) {
                $route = $routes[$validated['type']];

                return redirect()->route($route['route'], $route['params']($request));
            }

            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Terjadi kesalahan, tipe surat tidak dikenali.',
            ]);

            return redirect()->back();
        } catch (\Throwable $th) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Gagal mengirim nota dinas: '.$th->getMessage(),
            ]);

            return redirect()->back();
        }
    }

    public function storeClose(StoreMemoCloseRequest $request, CloseMemoAction $action): RedirectResponse
    {
        $user = $request->user();
        $currentPosition = $user->currentPosition()->first();
        $validated = $request->validated();

        try {
            $action->handle($user, $currentPosition, $validated);

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Nota dinas penutupan berhasil disimpan dan status surat ditutup.',
            ]);

            return redirect()->route('memo.index');
        } catch (\Throwable $th) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Gagal menutup surat: '.$th->getMessage(),
            ]);

            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Memo $recipient, string $letter_id): Response|RedirectResponse
    {
        $user = $request->user();
        $currentPosition = $user?->currentPosition()?->first();

        if (! $currentPosition || $recipient->to_position_id != $currentPosition->id) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Anda tidak memiliki akses ke nota dinas ini.',
            ]);

            return redirect()->back();
        }

        if ($recipient->letter()->where('letter_id', $letter_id)->doesntExist()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Surat tidak ditemukan atau tidak sesuai dengan nota dinas ini.',
            ]);

            return redirect()->back();
        }

        $from = $recipient->senderUser()->select('id', 'name')->first();

        $recipients = $this->getTimeline($recipient->letter_id);

        $letter = $recipient->letter()->with([
            'docs:id,letter_id,name',
        ])->firstOrFail();

        return Inertia::render('app/memo/Show', [
            'breadcrumbItems' => [
                [
                    'title' => 'Nota Dinas',
                    'route' => 'memo.index',
                ],
                [
                    'title' => 'Detail Nota Dinas',
                ],
            ],
            'from' => fn () => $from,
            'memo' => fn () => $recipient->only(
                'id',
                'letter_id',
                'letter_recipient_id',
                'is_cc',
                'received_at'
            ),
            'recipients' => fn () => $recipients,
            'letter' => fn () => $letter,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        Request $request,
        Memo $memo,
        string $letter_id,
        MarkMemoAsReadAction $action,
        SyncNotificationReadStateAction $sync
    ): RedirectResponse {
        try {
            $action->handle($memo);
            $sync->markForItem(
                $request->user(),
                'memo',
                $memo->id,
                route('memo.show', [
                    'recipient' => $memo->id,
                    'letter_id' => $letter_id,
                ]),
            );

            return redirect()->route('memo.show', ['recipient' => $memo->id, 'letter_id' => $letter_id]);
        } catch (\Throwable $th) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Gagal memperbarui status nota dinas: '.$th->getMessage(),
            ]);

            return redirect()->back();
        }
    }
}

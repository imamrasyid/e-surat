<?php

namespace App\Http\Controllers;

use App\Actions\Disposition\CreateDispositionAction;
use App\Actions\Disposition\MarkDispositionAsReadAction;
use App\Actions\Notification\SyncNotificationReadStateAction;
use App\Http\Requests\StoreDispositionRequest;
use App\Models\Disposition;
use App\Models\Followup;
use App\Models\Letter;
use App\Models\Position;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class DispositionController extends Controller
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
            return Inertia::render('app/disposition/Index', [
                'breadcrumbItems' => [
                    [
                        'title' => 'Disposisi',
                        'route' => 'dispo.index',
                    ],
                ],
                'dispo' => fn () => new LengthAwarePaginator([], 0, 15),
            ]);
        }

        $dispo = Disposition::query()
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

        return Inertia::render('app/disposition/Index', [
            'breadcrumbItems' => [
                [
                    'title' => 'Disposisi',
                    'route' => 'dispo.index',
                ],
            ],
            'dispo' => fn () => $dispo,
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

        $user = $request->user();
        $currentPosition = $user->currentPosition()->first();

        // --- Ambil posisi normal (hirarki bawahan) ---
        $positions = Position::query()
            ->select('id', 'name')
            ->where('parent_id', $currentPosition->id)
            ->where('is_active', true)
            ->orderBy('name');

        // --- Ambil hak khusus dari pivot model_has_special_dispositions ---
        $specialPositions = Position::query()
            ->select('positions.id', 'positions.name')
            ->join('model_has_special_dispositions as mh', 'mh.to_position_id', '=', 'positions.id')
            ->where('mh.model_id', $user->id)
            ->where('mh.is_active', true);

        // --- Gabungkan hasilnya ---
        $positions = $positions
            ->union($specialPositions)
            ->orderBy('name')
            ->get();

        $followup = Followup::query()
            ->select('id', 'name')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('app/disposition/Create', [
            'breadcrumbItems' => [
                [
                    'title' => 'Disposisi',
                    'route' => 'dispo.index',
                ],
                [
                    'title' => 'Tambah Baru',
                ],
            ],
            'letter' => fn () => $letter,
            'recipients' => fn () => $recipients,
            'recipient_id' => fn () => $recipient,
            'positions' => fn () => $positions,
            'followup' => fn () => $followup,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDispositionRequest $request, CreateDispositionAction $action): RedirectResponse
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
                'message' => 'Disposisi berhasil dikirim.',
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
                'message' => 'Gagal mengirim disposisi: '.$th->getMessage(),
            ]);

            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Disposition $recipient, string $letter_id): Response|RedirectResponse
    {
        $user = $request->user();
        $currentPosition = $user?->currentPosition()?->first();

        if (! $currentPosition || $recipient->to_position_id != $currentPosition->id) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Anda tidak memiliki akses ke disposisi ini.',
            ]);

            return redirect()->back();
        }

        if ($recipient->letter()->where('letter_id', $letter_id)->doesntExist()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Surat tidak ditemukan atau tidak sesuai dengan disposisi ini.',
            ]);

            return redirect()->back();
        }

        $from = $recipient->senderUser()->select('id', 'name')->first();

        $recipients = $this->getTimeline($recipient->letter_id);

        $letter = $recipient->letter()->with([
            'docs:id,letter_id,name',
        ])->firstOrFail();

        return Inertia::render('app/disposition/Show', [
            'breadcrumbItems' => [
                [
                    'title' => 'Disposisi',
                    'route' => 'dispo.index',
                ],
                [
                    'title' => 'Detail Disposisi',
                ],
            ],
            'from' => fn () => $from,
            'disposition' => fn () => $recipient->only(
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
        Disposition $disposition,
        string $letter_id,
        MarkDispositionAsReadAction $action,
        SyncNotificationReadStateAction $sync
    ): RedirectResponse {
        try {
            $action->handle($disposition);
            $sync->markForItem(
                $request->user(),
                'disposition',
                $disposition->id,
                route('dispo.show', [
                    'recipient' => $disposition->id,
                    'letter_id' => $letter_id,
                ]),
            );

            return redirect()->route('dispo.show', ['recipient' => $disposition->id, 'letter_id' => $letter_id]);
        } catch (\Throwable $th) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Gagal memperbarui status disposisi: '.$th->getMessage(),
            ]);

            return redirect()->back();
        }
    }
}

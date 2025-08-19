<?php

namespace App\Http\Controllers;

use App\Models\Memo;
use App\Models\Letter;
use App\Models\LetterDoc;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Response;
use Illuminate\Support\Facades\Request as FacadesRequest;

class MemoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $user = User::find(Auth::user()->id);
        $currentPosition = $user?->currentPosition()?->first();

        // Kalau user tidak punya currentPosition
        if (!$currentPosition) {
            return inertia('app/memo/Index', [
                'breadcrumbItems' => [
                    [
                        'title' => 'Nota Dinas',
                        'href' => '/nota-dinas',
                    ],
                ],
                'memo' => fn() => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15)
            ]);
        }

        $memo = Memo::query()
            ->where('to_position_id', $currentPosition->id)
            ->with([
                'fromPosition:id,name',
                'letter:id,letter_id,agenda,sender,number,subject'
            ])
            ->when(FacadesRequest::input('search'), function (Builder $query, $search) {
                $query->whereHas('letter', function (Builder $query) use ($search) {
                    $query->where('agenda', 'like', '%' . $search . '%');
                });
            })
            ->when(FacadesRequest::input('filter'), function (Builder $query, $filter) {
                if ($filter == 'read') {
                    $query->whereNotNull('read_at');
                } elseif ($filter == 'unread') {
                    $query->whereNull('read_at');
                }
            })
            ->orderBy('received_at', 'desc')
            ->paginate(15);

        return inertia('app/memo/Index', [
            'breadcrumbItems' => [
                [
                    'title' => 'Nota Dinas',
                    'href' => '/nota-dinas',
                ],
            ],
            'memo' => fn() => $memo,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(string $recipient, string $key): Response
    {
        $keyDecode = base64_decode($key);
        $letter_id = substr($keyDecode, -26);

        $letter = Letter::where('letter_id', $letter_id)->where('status', 'open')->firstOrFail();

        $recipients = $this->getTimeline($letter->id);

        $positions = Position::query()
            ->select('id', 'name')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();
        
        return inertia('app/memo/Create', [
            'breadcrumbItems' => [
                [
                    'title' => 'Nota Dinas',
                    'href' => '/nota-dinas',
                ],
                [
                    'title' => 'Tambah Baru',
                    'href' => '',
                ],
            ],
            'letter' => fn() => $letter,
            'recipients' => fn() => $recipients,
            'recipient_id' => fn() => $recipient,
            'positions' => fn() => $positions,
        ]);
    }
    public function createClose(Memo $memo, string $letter_id): Response
    {
        $letter = $memo->letter()->where('status', 'open')->firstOrFail();

        $recipients = $this->getTimeline($letter->id);
        
        return inertia('app/memo/CreateClose', [
            'breadcrumbItems' => [
                [
                    'title' => 'Nota Dinas',
                    'href' => '/nota-dinas',
                ],
                [
                    'title' => 'Penutupan Surat',
                    'href' => '',
                ],
            ],
            'letter' => fn() => $letter,
            'recipients' => fn() => $recipients,
            'memo' => fn() => $memo,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = User::find(Auth::user()->id);
        $currentPosition = $user->currentPosition()->first();

        $request->validate([
            'type'                => 'required|string',
            'letter_id_primary'   => 'required|integer',
            'letter_id'           => 'required|string',
            'letter_recipient_id' => 'required|integer',
            'letter_receipt_date' => 'required|string',
            'penerima'            => 'required|array|min:1',
            'penerima.*.id'       => 'required|integer',
            'penerima.*.name'     => 'required|string',
            'penerima_cc'         => 'nullable|array',
            'penerima_cc.*.id'    => 'nullable|integer',
            'penerima_cc.*.name'  => 'nullable|string',
            'catatan'             => 'nullable|string',
            'lampiran'            => 'nullable|array',
            'lampiran.*'          => 'nullable|file|mimes:pdf'
        ]);

        DB::beginTransaction();

        try {
            //INSERT PENERIMA
            foreach ($request->penerima as $penerima) {
                Memo::create([
                    'letter_id'           => $request->letter_id_primary,
                    'letter_recipient_id' => $request->letter_recipient_id,
                    'from_user_id'        => $user->id,
                    'from_position_id'    => $currentPosition->id,
                    'to_position_id'      => $penerima['id'],
                    'instruction'         => $request->catatan,
                    'received_at'         => now(),
                ]);
            }
            if ($request->penerima_cc) {
                foreach ($request->penerima_cc as $penerima) {
                    Memo::create([
                        'letter_id'           => $request->letter_id_primary,
                        'letter_recipient_id' => $request->letter_recipient_id,
                        'is_cc'               => 1,
                        'from_user_id'        => $user->id,
                        'from_position_id'    => $currentPosition->id,
                        'to_position_id'      => $penerima['id'],
                        'received_at'         => now(),
                    ]);
                }
            }

            //INSERT LAMPIRAN
            if ($request->HasFile('lampiran')) {
                foreach ($request->lampiran as $lampiran) {
                    $path = 'attachment/' . $request->letter_receipt_date;
                    $filename = 'MMO' . now()->format('His') . '_' . $lampiran->getClientOriginalName();
                    Storage::disk('public')->putFileAs($path, $lampiran, $filename);
                    
                    $storeUrl = "/storage/{$path}/{$filename}";
                    LetterDoc::create([
                        'letter_id' => $request->letter_id_primary,
                        'name'      => $filename,
                        'url'       => $storeUrl,
                    ]);
                }
            }
            
            DB::commit();

            $routes = [
                'primary' => [
                    'route' => 'inbox.primary.show',
                    'params' => fn ($req) => [
                        'recipient' => $req->letter_recipient_id,
                        'letter_id' => $req->letter_id,
                    ]
                ],
                'undangan' => [
                    'route' => 'inbox.undangan.show',
                    'params' => fn ($req) => [
                        'recipient' => $req->letter_recipient_id,
                        'letter_id' => $req->letter_id,
                    ]
                ],
                'disposisi' => [
                    'route' => 'dispo.index',
                    'params' => fn () => []
                ],
                'memo' => [
                    'route' => 'memo.index',
                    'params' => fn () => []
                ],
            ];

            if (isset($routes[$request->type])) {
                $route = $routes[$request->type];
                return redirect()->route($route['route'], $route['params']($request));
            }

            return redirect()->back()->withErrors(['confirm' => 'Terjadi kesalahan, tipe surat tidak dikenali.']);

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->withErrors(['confirm' => $th->getMessage()]);
        }        
    }
    public function storeClose(Request $request): RedirectResponse
    {
        $user = User::find(Auth::user()->id);
        $currentPosition = $user->currentPosition()->first();

        $request->validate([
            'letter_id_primary'   => 'required|integer',
            'letter_recipient_id' => 'required|integer',
            'to_position_id'      => 'required|integer',
            'catatan'             => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            //INSERT MEMO
            Memo::create([
                'letter_id'           => $request->letter_id_primary,
                'letter_recipient_id' => $request->letter_recipient_id,
                'from_user_id'        => $user->id,
                'from_position_id'    => $currentPosition->id,
                'to_position_id'      => $request->to_position_id,
                'instruction'         => $request->catatan,
                'received_at'         => now(),
            ]);

            //UPDATE LETTER STATUS
            $letter = Letter::findOrFail($request->letter_id_primary);
            $letter->update([
                'status' => 'close',
            ]);
            
            DB::commit();

            return redirect()->route('memo.index');

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->withErrors(['confirm' => $th->getMessage()]);
        }        
    }

    /**
     * Display the specified resource.
     */
    public function show(Memo $recipient, string $letter_id): Response | RedirectResponse
    {
        $user = User::find(Auth::user()->id);
        $currentPosition = $user->currentPosition()->first();

        if ($recipient->to_position_id != $currentPosition->id) {
            return redirect()->back()->withErrors(['confirm' => 'Anda tidak memiliki akses ke disposisi ini.']);
        }

        if ($recipient->letter()->where('letter_id', $letter_id)->doesntExist()) {
            return redirect()->back()->withErrors(['confirm' => 'Surat tidak ditemukan atau tidak sesuai dengan disposisi ini.']);
        }

        $from = $recipient->senderUser()->select('id', 'name')->first();

        $recipients = $this->getTimeline($recipient->letter_id);

        $letter = $recipient->letter()->with([
            'docs:id,letter_id,name,url'
        ])->firstOrFail();

        return inertia('app/memo/Show', [
            'breadcrumbItems' => [
                [
                    'title' => 'Nota Dinas',
                    'href' => '/nota-dinas',
                ],
                [
                    'title' => 'Detail Nota Dinas',
                    'href' => '',
                ],
            ],
            'from' => fn() => $from,
            'memo' => fn() => $recipient->only(
                'id',
                'letter_id',
                'letter_recipient_id',
                'is_cc',
                'received_at'
            ),
            'recipients' => fn() => $recipients,
            'letter' => fn() => $letter,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Memo $memo, string $letter_id): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $memo->update(['read_at' => now()]);

            DB::commit();

            return redirect()->route('memo.show', (['recipient' => $memo->id, 'letter_id' => $letter_id]));

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->withErrors(['confirm' => $th->getMessage()]);
        }
    }
}

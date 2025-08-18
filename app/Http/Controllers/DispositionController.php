<?php

namespace App\Http\Controllers;

use App\Models\Disposition;
use App\Models\Followup;
use App\Models\Letter;
use App\Models\LetterDoc;
use App\Models\LetterRecipient;
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

class DispositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $user = User::find(Auth::user()->id);
        $currentPosition = $user->currentPosition()->first();

        $dispo = Disposition::query()
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

        return inertia('app/disposition/Index', [
            'breadcrumbItems' => [
                [
                    'title' => 'Disposisi',
                    'href' => '/disposition',
                ],
            ],
            'dispo' => fn() => $dispo,
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

        $user = User::find(Auth::user()->id);
        $currentPosition = $user->currentPosition()->first();

        // --- Ambil posisi normal (hirarki bawahan) ---
        $positions = Position::query()
            ->select('id', 'name')
            ->where('parent_id', $currentPosition->id)
            ->where('is_active', 1)
            ->orderBy('name');

        // --- Ambil hak khusus dari pivot model_has_special_dispositions ---
        $specialPositions = Position::query()
            ->select('positions.id', 'positions.name')
            ->join('model_has_special_dispositions as mh', 'mh.to_position_id', '=', 'positions.id')
            ->where('mh.model_id', $user->id)
            ->where('mh.is_active', 1);

        // --- Gabungkan hasilnya ---
        $positions = $positions
            ->union($specialPositions)
            ->orderBy('name')
            ->get();

        $followup = Followup::query()
            ->select('id', 'name')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();
        
        return inertia('app/disposition/Create', [
            'breadcrumbItems' => [
                [
                    'title' => 'Disposisi',
                    'href' => '/disposition',
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
            'followup' => fn() => $followup,
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
            'followup'            => 'nullable|array',
            'followup.*.id'       => 'nullable|integer',
            'followup.*.name'     => 'nullable|string',
            'lampiran'            => 'nullable|array',
            'lampiran.*'          => 'nullable|file|mimes:pdf'
        ]);

        $followups = null;
        if ($request->followup) {
            $followups = implode('; ', array_map(function ($entry) {
                    return ($entry['name']);
                }, $request->followup));
        }

        DB::beginTransaction();

        try {
            //INSERT PENERIMA
            foreach ($request->penerima as $penerima) {
                Disposition::create([
                    'letter_id'           => $request->letter_id_primary,
                    'letter_recipient_id' => $request->letter_recipient_id,
                    'from_user_id'        => $user->id,
                    'from_position_id'    => $currentPosition->id,
                    'to_position_id'      => $penerima['id'],
                    'instruction'         => $request->catatan,
                    'followup'            => $followups,
                    'received_at'         => now(),
                ]);
            }
            if ($request->penerima_cc) {
                foreach ($request->penerima_cc as $penerima) {
                    Disposition::create([
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
                    $filename = 'DSP' . now()->format('His') . '_' . $lampiran->getClientOriginalName();
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

    /**
     * Display the specified resource.
     */
    public function show(Disposition $recipient, string $letter_id): Response | RedirectResponse
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

        return inertia('app/disposition/Show', [
            'breadcrumbItems' => [
                [
                    'title' => 'Disposisi',
                    'href' => '/disposition',
                ],
                [
                    'title' => 'Detail Disposisi',
                    'href' => '',
                ],
            ],
            'from' => fn() => $from,
            'disposition' => fn() => $recipient->only(
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
    public function update(Disposition $disposition, string $letter_id): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $disposition->update(['read_at' => now()]);

            DB::commit();

            return redirect()->route('dispo.show', (['recipient' => $disposition->id, 'letter_id' => $letter_id]));

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->withErrors(['confirm' => $th->getMessage()]);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Followup;
use App\Models\Letter;
use App\Models\LetterRecipient;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class DispositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return inertia('app/disposition/Index', [
            'breadcrumbItems' => [
                [
                    'title' => 'Disposisi',
                    'href' => '/disposition',
                ],
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(string $recipient_id, string $key): Response
    {
        $keyDecode = base64_decode($key);
        $letter_id = substr($keyDecode, -26);

        $letter = Letter::where('letter_id', $letter_id)->where('status', 'open')->firstOrFail();

        $letter_recipients = LetterRecipient::where('letter_id', $letter->id)
            ->with(['fromPosition:id,name', 'toPosition:id,name'])
            ->orderBy('received_at')
            ->get();

        $user = User::find(Auth::user()->id);
        $currentPosition = $user->currentPosition()->first();
        $positions = Position::query()
            ->select('id', 'name')
            ->where('parent_id', $currentPosition->id)
            ->where('is_active', 1)
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
            'letter_recipients' => fn() => $letter_recipients,
            'recipient_id' => fn() => $recipient_id,
            'positions' => fn() => $positions,
            'followup' => fn() => $followup,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $routes = [
            'primary' => 'inbox.primary.show',
            'undangan' => 'inbox.undangan.show',
            'disposisi' => 'dispo.show',
        ];

        if (array_key_exists($request->type, $routes)) {
            return redirect()->route($routes[$request->type], [
                'letter_recipient_id' => $request->letter_recipient_id,
                'letter_id' => $request->letter_id,
            ]);
        }

        return redirect()->back()->withErrors(['confirm' => 'Terjadi kesalahan, tipe surat tidak dikenali.']);
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

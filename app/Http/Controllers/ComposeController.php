<?php

namespace App\Http\Controllers;

use App\Actions\Letter\CreateComposeLetterAction;
use App\Http\Requests\StoreComposeRequest;
use App\Models\Position;
use App\Models\RefKdKlasifikasi;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ComposeController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $kdKlasifikasi = RefKdKlasifikasi::query()
            ->select('id', 'kode', 'nama', 'is_active')
            ->where('is_active', true)
            ->orderBy('nama')
            ->get();

        $positions = Position::query()
            ->select('id', 'name')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('app/Compose', [
            'kdKlasifikasi' => fn () => $kdKlasifikasi,
            'positions' => fn () => $positions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreComposeRequest $request, CreateComposeLetterAction $action): RedirectResponse
    {
        $user = $request->user();
        $currentPosition = $user?->currentPosition()->first();

        if (! $currentPosition) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Anda belum memiliki jabatan aktif untuk membuat surat.',
            ]);

            return back();
        }

        try {
            $action->handle(
                $user,
                $currentPosition,
                $request->validated(),
                $request->file('lampiran', [])
            );

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Surat berhasil dibuat dan dikirimkan.',
            ]);

            return to_route('app.compose.create');
        } catch (Throwable $th) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Gagal membuat surat: '.$th->getMessage(),
            ]);

            return back();
        }
    }
}

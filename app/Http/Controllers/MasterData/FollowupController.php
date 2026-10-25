<?php

namespace App\Http\Controllers\MasterData;

use App\Actions\MasterData\Followup\CreateFollowupAction;
use App\Actions\MasterData\Followup\UpdateFollowupAction;
use App\Http\Controllers\Controller;
use App\Models\Followup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class FollowupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $data = Followup::query()
            ->select('id', 'name', 'is_active')
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $query->whereLike('name', '%'.$request->input('search').'%');
            })
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('master/followup/Index', [
            'breadcrumbItems' => [
                [
                    'title' => 'Master Data',
                    'route' => 'ref.master-data',
                ],
                [
                    'title' => 'Followup',
                    'route' => 'ref.followup.index',
                ],
            ],
            'data' => fn () => $data,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('master/followup/Create', [
            'breadcrumbItems' => [
                [
                    'title' => 'Master Data',
                    'route' => 'ref.master-data',
                ],
                [
                    'title' => 'Followup',
                    'route' => 'ref.followup.index',
                ],
                [
                    'title' => 'Tambah Baru',
                ],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, CreateFollowupAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ], [
            'name.required' => 'Nama followup wajib diisi.',
            'name.string' => 'Nama followup harus berupa teks.',
            'name.max' => 'Nama followup maksimal 255 karakter.',
        ]);

        try {
            $action->handle($validated);

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Data followup berhasil disimpan.',
            ]);

            return to_route('ref.followup.index');
        } catch (Throwable $th) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Gagal menyimpan data: '.$th->getMessage(),
            ]);

            return back();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Followup $followup): Response
    {
        return Inertia::render('master/followup/Edit', [
            'breadcrumbItems' => [
                [
                    'title' => 'Master Data',
                    'route' => 'ref.master-data',
                ],
                [
                    'title' => 'Followup',
                    'route' => 'ref.followup.index',
                ],
                [
                    'title' => 'Perbarui',
                ],
            ],
            'data' => fn () => $followup,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        Request $request,
        Followup $followup,
        UpdateFollowupAction $action
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ], [
            'name.required' => 'Nama followup wajib diisi.',
            'name.string' => 'Nama followup harus berupa teks.',
            'name.max' => 'Nama followup maksimal 255 karakter.',
            'is_active.required' => 'Status aktif wajib dipilih.',
            'is_active.boolean' => 'Status aktif tidak valid.',
        ]);

        try {
            $action->handle($followup, $validated);

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Data followup berhasil diperbarui.',
            ]);

            return to_route('ref.followup.index');
        } catch (Throwable $th) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Gagal memperbarui data: '.$th->getMessage(),
            ]);

            return back();
        }
    }
}

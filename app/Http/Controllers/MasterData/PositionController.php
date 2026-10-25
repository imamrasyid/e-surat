<?php

namespace App\Http\Controllers\MasterData;

use App\Actions\MasterData\Position\CreatePositionAction;
use App\Actions\MasterData\Position\UpdatePositionAction;
use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $data = Position::query()
            ->select('id', 'name', 'parent_id', 'is_active')
            ->with('parent:id,name')
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $query->whereLike('name', '%'.$request->input('search').'%');
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Position $position) => [
                'id' => $position->id,
                'name' => $position->name,
                'parent_id' => $position->parent_id,
                'parent_name' => $position->parent?->name,
                'is_active' => $position->is_active,
            ]);

        return Inertia::render('master/jabatan/Index', [
            'breadcrumbItems' => [
                [
                    'title' => 'Master Data',
                    'route' => 'ref.master-data',
                ],
                [
                    'title' => 'Jabatan',
                    'route' => 'ref.position.index',
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
        $positions = Position::query()
            ->select('id', 'name')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('master/jabatan/Create', [
            'breadcrumbItems' => [
                [
                    'title' => 'Master Data',
                    'route' => 'ref.master-data',
                ],
                [
                    'title' => 'Jabatan',
                    'route' => 'ref.position.index',
                ],
                [
                    'title' => 'Tambah Baru',
                ],
            ],
            'positions' => fn () => $positions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, CreatePositionAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:positions,id'],
        ], [
            'name.required' => 'Nama jabatan wajib diisi.',
            'name.string' => 'Nama jabatan harus berupa teks.',
            'name.max' => 'Nama jabatan maksimal 255 karakter.',
            'parent_id.exists' => 'Jabatan atasan yang dipilih tidak valid.',
        ]);

        try {
            $action->handle($validated);

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Data jabatan berhasil disimpan.',
            ]);

            return to_route('ref.position.index');
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
    public function edit(Position $position): Response
    {
        return Inertia::render('master/jabatan/Edit', [
            'breadcrumbItems' => [
                [
                    'title' => 'Master Data',
                    'route' => 'ref.master-data',
                ],
                [
                    'title' => 'Jabatan',
                    'route' => 'ref.position.index',
                ],
                [
                    'title' => 'Perbarui',
                ],
            ],
            'data' => fn () => $position,
            'positions' => fn () => Position::query()
                ->select('id', 'name')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        Request $request,
        Position $position,
        UpdatePositionAction $action
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:positions,id'],
            'is_active' => ['required', 'boolean'],
        ], [
            'name.required' => 'Nama jabatan wajib diisi.',
            'name.string' => 'Nama jabatan harus berupa teks.',
            'name.max' => 'Nama jabatan maksimal 255 karakter.',
            'parent_id.exists' => 'Jabatan atasan yang dipilih tidak valid.',
            'is_active.required' => 'Status aktif wajib dipilih.',
            'is_active.boolean' => 'Status aktif tidak valid.',
        ]);

        try {
            $action->handle($position, $validated);

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Data jabatan berhasil diperbarui.',
            ]);

            return to_route('ref.position.index');
        } catch (Throwable $th) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Gagal memperbarui data: '.$th->getMessage(),
            ]);

            return back();
        }
    }
}

<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Response;
use Illuminate\Support\Facades\Request as FacadesRequest;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $data = Position::query()
            ->select('id', 'name', 'parent_id', 'is_active')
            ->where(function(Builder $query) {
                $search = FacadesRequest::input('search');
                if ($search) {
                    $query->whereLike('name','%'.$search.'%');
                }
            })
            ->orderBy('name')
            ->paginate(10)
            ->through(function ($position) {
                return [
                    'id' => $position->id,
                    'name' => $position->name,
                    'parent_id' => $position->parent_id,
                    'parent_name' => $position->parent_name, // accessor dari model
                    'is_active' => $position->is_active,
                ];
            });

        return inertia('master/jabatan/Index', [
            'breadcrumbItems' => [
                [
                    'title' => 'Jabatan',
                    'href' => '/master-data/jabatan',
                ],
            ],
            'data' => fn() => $data,
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

        return inertia('master/jabatan/Create', [
            'breadcrumbItems' => [
                [
                    'title' => 'Jabatan',
                    'href' => '/master-data/jabatan',
                ],
                [
                    'title' => 'Tambah Baru',
                    'href' => '/master-data/jabatan/create',
                ],
            ],
            'positions' => fn() => $positions
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:positions,id',
        ]);

        DB::beginTransaction();

        try {
            $data = $request->all();

            Position::create($data);

            DB::commit();

            return redirect()->route('ref.position.index');

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->withErrors(['confirm' => $th->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Position $position): Response
    {
        return inertia('master/jabatan/Edit', [
            'breadcrumbItems' => [
                [
                    'title' => 'Jabatan',
                    'href' => '/master-data/jabatan',
                ],
                [
                    'title' => 'Perbarui',
                    'href' => '/master-data/jabatan/edit',
                ],
            ],
            'data' => fn() => $position,
            'positions' => fn() => Position::query()
                ->select('id', 'name')
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Position $position): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:positions,id',
            'is_active' => 'required|boolean'
        ]);

        DB::beginTransaction();

        try {
            $position->update([
                'name' => $request->name,
                'parent_id' => $request->parent_id,
                'is_active' => $request->is_active
            ]);

            DB::commit();

            return redirect()->route('ref.position.index');

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->withErrors(['confirm' => $th->getMessage()]);
        }
    }
}

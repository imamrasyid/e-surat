<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Followup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Inertia\Response;

class FollowupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $data = Followup::query()
            ->select('id', 'name', 'is_active')
            ->where(function (Builder $query) {
                $search = FacadesRequest::input('search');
                if ($search) {
                    $query->whereLike('name', '%'.$search.'%');
                }
            })
            ->paginate(10);

        return inertia('master/followup/Index', [
            'breadcrumbItems' => [
                [
                    'title' => 'Followup',
                    'href' => '/master-data/followup',
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
        return inertia('master/followup/Create', [
            'breadcrumbItems' => [
                [
                    'title' => 'Followup',
                    'href' => '/master-data/followup',
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
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $data = $request->all();

            Followup::create($data);

            DB::commit();

            return redirect()->route('ref.followup.index');

        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->back()->withErrors(['confirm' => $th->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Followup $followup): Response
    {
        return inertia('master/followup/Edit', [
            'breadcrumbItems' => [
                [
                    'title' => 'Followup',
                    'href' => '/master-data/followup',
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
    public function update(Request $request, Followup $followup): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        DB::beginTransaction();

        try {
            $followup->update([
                'name' => $request->name,
                'is_active' => $request->is_active,
            ]);

            DB::commit();

            return redirect()->route('ref.followup.index');

        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->back()->withErrors(['confirm' => $th->getMessage()]);
        }
    }
}

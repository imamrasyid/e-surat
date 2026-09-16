<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $data = Role::query()
            ->select('id', 'name')
            ->where(function (Builder $query) {
                $search = FacadesRequest::input('search');
                if ($search) {
                    $query->whereLike('name', '%'.$search.'%');
                }
            })
            ->paginate(10);

        return inertia('master/role/Index', [
            'breadcrumbItems' => [
                [
                    'title' => 'Followup',
                    'href' => '/master-data/roles',
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
        return inertia('master/role/Create', [
            'breadcrumbItems' => [
                [
                    'title' => 'Role',
                    'href' => '/master-data/roles',
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

            Role::create($data);

            DB::commit();

            return redirect()->route('ref.role.index');

        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->back()->withErrors(['confirm' => $th->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role): Response
    {
        $permissions = Permission::all();

        return inertia('master/role/Edit', [
            'breadcrumbItems' => [
                [
                    'title' => 'Role',
                    'href' => '/master-data/roles',
                ],
                [
                    'title' => 'Perbarui',
                ],
            ],
            'data' => fn () => $role,
            'rolePermissions' => fn () => $role->permissions()->select('id', 'name')->get(),
            'permissions' => fn () => $permissions,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $role->update([
                'name' => $request->name,
            ]);

            DB::commit();

            return redirect()->route('ref.role.index');

        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->back()->withErrors(['confirm' => $th->getMessage()]);
        }
    }

    public function updatePermission(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:'.Permission::class.',id',
        ]);

        DB::beginTransaction();

        try {
            $role->syncPermissions($validated['permissions']);

            DB::commit();

            return redirect()->route('ref.role.index');

        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->back()->withErrors(['confirm' => $th->getMessage()]);
        }
    }
}

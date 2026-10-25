<?php

namespace App\Http\Controllers\MasterData;

use App\Actions\MasterData\Role\CreateRoleAction;
use App\Actions\MasterData\Role\UpdateRoleAction;
use App\Actions\MasterData\Role\UpdateRolePermissionsAction;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Throwable;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $data = Role::query()
            ->select('id', 'name')
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $query->whereLike('name', '%'.$request->input('search').'%');
            })
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('master/role/Index', [
            'breadcrumbItems' => [
                [
                    'title' => 'Master Data',
                    'route' => 'ref.master-data',
                ],
                [
                    'title' => 'Role',
                    'route' => 'ref.role.index',
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
        return Inertia::render('master/role/Create', [
            'breadcrumbItems' => [
                [
                    'title' => 'Master Data',
                    'route' => 'ref.master-data',
                ],
                [
                    'title' => 'Role',
                    'route' => 'ref.role.index',
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
    public function store(Request $request, CreateRoleAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ], [
            'name.required' => 'Nama role wajib diisi.',
            'name.string' => 'Nama role harus berupa teks.',
            'name.max' => 'Nama role maksimal 255 karakter.',
        ]);

        try {
            $action->handle($validated);

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Role berhasil disimpan.',
            ]);

            return to_route('ref.role.index');
        } catch (Throwable $th) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Gagal menyimpan role: '.$th->getMessage(),
            ]);

            return back();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role): Response
    {
        $permissions = Permission::query()->select('id', 'name')->get();

        return Inertia::render('master/role/Edit', [
            'breadcrumbItems' => [
                [
                    'title' => 'Master Data',
                    'route' => 'ref.master-data',
                ],
                [
                    'title' => 'Role',
                    'route' => 'ref.role.index',
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
    public function update(
        Request $request,
        Role $role,
        UpdateRoleAction $action
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ], [
            'name.required' => 'Nama role wajib diisi.',
            'name.string' => 'Nama role harus berupa teks.',
            'name.max' => 'Nama role maksimal 255 karakter.',
        ]);

        try {
            $action->handle($role, $validated);

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Role berhasil diperbarui.',
            ]);

            return to_route('ref.role.index');
        } catch (Throwable $th) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Gagal memperbarui role: '.$th->getMessage(),
            ]);

            return back();
        }
    }

    public function updatePermission(
        Request $request,
        Role $role,
        UpdateRolePermissionsAction $action
    ): RedirectResponse {
        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:'.Permission::class.',id'],
        ], [
            'permissions.array' => 'Format daftar izin harus berupa array.',
            'permissions.*.exists' => 'Izin yang dipilih tidak valid.',
        ]);

        try {
            $action->handle($role, $validated['permissions'] ?? []);

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Hak akses role berhasil diperbarui.',
            ]);

            return to_route('ref.role.index');
        } catch (Throwable $th) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Gagal memperbarui hak akses role: '.$th->getMessage(),
            ]);

            return back();
        }
    }
}

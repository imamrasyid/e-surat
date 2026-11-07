<?php

namespace App\Http\Controllers\MasterData;

use App\Actions\MasterData\User\CreateUserAction;
use App\Actions\MasterData\User\UpdateUserAction;
use App\Actions\MasterData\User\UpdateUserPositionAction;
use App\Actions\MasterData\User\UpdateUserRoleAction;
use App\Actions\MasterData\User\UpdateUserSpecialDispositionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreUserRequest;
use App\Http\Requests\MasterData\UpdateUserRequest;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Throwable;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $data = User::query()
            ->select('id', 'name', 'username', 'email', 'is_active')
            ->where('id', '!=', User::SUPER_ADMIN_ID)
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $search = $request->input('search');
                $query->where(function (Builder $q) use ($search) {
                    $q->whereLike('name', '%'.$search.'%')
                        ->orWhereLike('username', '%'.$search.'%')
                        ->orWhereLike('email', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('master/pengguna/Index', [
            'breadcrumbItems' => [
                [
                    'title' => 'Master Data',
                    'route' => 'ref.master-data',
                ],
                [
                    'title' => 'Pengguna',
                    'route' => 'ref.user.index',
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

        $roles = Role::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('master/pengguna/Create', [
            'breadcrumbItems' => [
                [
                    'title' => 'Master Data',
                    'route' => 'ref.master-data',
                ],
                [
                    'title' => 'Pengguna',
                    'route' => 'ref.user.index',
                ],
                [
                    'title' => 'Tambah Baru',
                ],
            ],
            'positions' => fn () => $positions,
            'roles' => fn () => $roles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request, CreateUserAction $action): RedirectResponse
    {
        /** @var array{name: string, username: string, position_id: int, role_id: int} $validated */
        $validated = $request->validated();

        try {
            $action->handle($validated);

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Pengguna berhasil ditambahkan.',
            ]);

            return to_route('ref.user.index');
        } catch (Throwable $th) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Gagal menambahkan pengguna: '.$th->getMessage(),
            ]);

            return back();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): Response|RedirectResponse
    {
        if ($user->id === User::SUPER_ADMIN_ID) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Akun Super Admin tidak dapat diubah.',
            ]);

            return back();
        }

        return Inertia::render('master/pengguna/Edit', [
            'breadcrumbItems' => [
                [
                    'title' => 'Master Data',
                    'route' => 'ref.master-data',
                ],
                [
                    'title' => 'Pengguna',
                    'route' => 'ref.user.index',
                ],
                [
                    'title' => 'Perbarui',
                ],
            ],
            'data' => $user,
        ]);
    }

    public function editPosition(User $user): Response|RedirectResponse
    {
        if ($user->id === User::SUPER_ADMIN_ID) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Akun Super Admin tidak dapat diubah.',
            ]);

            return back();
        }

        $currentPosition = $user->currentPosition()->first()?->only('name');

        $positions = Position::query()
            ->select('id', 'name')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('master/pengguna/Jabatan', [
            'breadcrumbItems' => [
                [
                    'title' => 'Master Data',
                    'route' => 'ref.master-data',
                ],
                [
                    'title' => 'Pengguna',
                    'route' => 'ref.user.index',
                ],
                [
                    'title' => 'Jabatan',
                ],
            ],
            'data' => $user,
            'currentPosition' => fn () => $currentPosition,
            'positions' => fn () => $positions,
            'specialDispositions' => fn () => $user->specialDispositions()->get(),
        ]);
    }

    public function editRole(string $user_id): Response|RedirectResponse
    {
        if ((int) $user_id === User::SUPER_ADMIN_ID) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Akun Super Admin tidak dapat diubah.',
            ]);

            return back();
        }

        $user = User::with('roles')->select('id', 'username', 'email', 'name')->findOrFail($user_id);
        $roles = Role::query()->select('id', 'name')->get();

        return Inertia::render('master/pengguna/Role', [
            'breadcrumbItems' => [
                [
                    'title' => 'Master Data',
                    'route' => 'ref.master-data',
                ],
                [
                    'title' => 'Pengguna',
                    'route' => 'ref.user.index',
                ],
                [
                    'title' => 'Role',
                ],
            ],
            'data' => $user,
            'roles' => fn () => $roles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateUserRequest $request,
        User $user,
        UpdateUserAction $action
    ): RedirectResponse {
        if ($user->id === User::SUPER_ADMIN_ID) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Akun Super Admin tidak dapat diubah.',
            ]);

            return back();
        }

        /** @var array{name: string, username: string, is_active: bool, password?: string|null} $validated */
        $validated = $request->validated();

        try {
            $action->handle($user, $validated);

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Pengguna berhasil diperbarui.',
            ]);

            return to_route('ref.user.index');
        } catch (Throwable $th) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Gagal memperbarui pengguna: '.$th->getMessage(),
            ]);

            return back();
        }
    }

    public function updatePosition(
        Request $request,
        User $user,
        UpdateUserPositionAction $action
    ): RedirectResponse {
        if ($user->id === User::SUPER_ADMIN_ID) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Akun Super Admin tidak dapat diubah.',
            ]);

            return back();
        }

        $validated = $request->validate([
            'positionId' => ['required', 'exists:'.Position::class.',id'],
        ], [
            'positionId.required' => 'Jabatan wajib dipilih.',
            'positionId.exists' => 'Jabatan yang dipilih tidak valid.',
        ]);

        try {
            $action->handle($user, $validated['positionId']);

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Jabatan pengguna berhasil diperbarui.',
            ]);

            return to_route('ref.user.index');
        } catch (Throwable $th) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Gagal memperbarui jabatan pengguna: '.$th->getMessage(),
            ]);

            return back();
        }
    }

    public function updateSpecialDisposition(
        Request $request,
        User $user,
        UpdateUserSpecialDispositionAction $action
    ): RedirectResponse {
        if ($user->id === User::SUPER_ADMIN_ID) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Akun Super Admin tidak dapat diubah.',
            ]);

            return back();
        }

        $validated = $request->validate([
            'position_ids' => ['nullable', 'array'],
            'position_ids.*' => ['exists:'.Position::class.',id'],
        ], [
            'position_ids.array' => 'Format jabatan disposisi khusus harus berupa array.',
            'position_ids.*.exists' => 'Jabatan disposisi yang dipilih tidak valid.',
        ]);

        try {
            $action->handle($user, $validated['position_ids'] ?? []);

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Disposisi khusus pengguna berhasil diperbarui.',
            ]);

            return to_route('ref.user.index');
        } catch (Throwable $th) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Gagal memperbarui disposisi khusus: '.$th->getMessage(),
            ]);

            return back();
        }
    }

    public function updateRole(
        Request $request,
        User $user,
        UpdateUserRoleAction $action
    ): RedirectResponse {
        if ($user->id === User::SUPER_ADMIN_ID) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Akun Super Admin tidak dapat diubah.',
            ]);

            return back();
        }

        $validated = $request->validate([
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:'.Role::class.',id'],
        ], [
            'roles.array' => 'Format role harus berupa array.',
            'roles.*.exists' => 'Role yang dipilih tidak valid.',
        ]);

        try {
            $action->handle($user, $validated['roles'] ?? []);

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Role pengguna berhasil diperbarui.',
            ]);

            return to_route('ref.user.index');
        } catch (Throwable $th) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Gagal memperbarui role pengguna: '.$th->getMessage(),
            ]);

            return back();
        }
    }
}

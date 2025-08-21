<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Response;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $data = User::query()
            ->select('id', 'name', 'email', 'is_active')
            ->where(function(Builder $query) {
                $search = FacadesRequest::input('search');
                if ($search) {
                    $query->whereLike('name','%'.$search.'%')
                        ->orWhereLike('email','%'.$search.'%');
                }
            })
            ->orderBy('name')
            ->paginate(10);

        return inertia('master/pengguna/Index', [
            'breadcrumbItems' => [
                [
                    'title' => 'Pengguna',
                    'href' => '/master-data/pengguna',
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
        return inertia('master/pengguna/Create', [
            'breadcrumbItems' => [
                [
                    'title' => 'Pengguna',
                    'href' => '/master-data/pengguna',
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
            'email' => 'required|string|lowercase|max:10|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        DB::beginTransaction();

        try {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            DB::commit();

            return redirect()->route('ref.user.index');

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->withErrors(['confirm' => $th->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): Response
    {
        return inertia('master/pengguna/Edit', [
            'breadcrumbItems' => [
                [
                    'title' => 'Pengguna',
                    'href' => '/master-data/pengguna',
                ],
                [
                    'title' => 'Perbarui',
                ],
            ],
            'data' => $user,
        ]);
    }
    public function editPosition(User $user): Response
    {
        $currentPosition = $user->currentPosition()->first();
        if ($currentPosition) {
            $currentPosition = $currentPosition->only('name');
        }

        $positions = Position::query()
                ->select('id', 'name')
                ->where('is_active', 1)
                ->orderBy('name')
                ->get();

        return inertia('master/pengguna/Jabatan', [
            'breadcrumbItems' => [
                [
                    'title' => 'Pengguna',
                    'href' => '/master-data/pengguna',
                ],
                [
                    'title' => 'Jabatan',
                ],
            ],
            'data' => $user,
            'currentPosition' => fn() => $currentPosition,
            'positions' => fn() => $positions,
            'specialDispositions' => fn() => $user->specialDispositions()->get(),
        ]);
    }
    public function editRole(string $user_id): Response
    {
        $user = User::with('roles')->select('id', 'email', 'name')->findOrFail($user_id);
        $roles = Role::all();

        return inertia('master/pengguna/Role', [
            'breadcrumbItems' => [
                [
                    'title' => 'Pengguna',
                    'href' => '/master-data/pengguna',
                ],
                [
                    'title' => 'Role',
                ],
            ],
            'data' => $user,
            'roles' => fn() => $roles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|max:10|unique:'.User::class.',email,'.$user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'is_active' => 'required|digits:1',
        ]);

        DB::beginTransaction();

        try {
            $emailChanged = $request->email !== $user->email;

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->filled('password') ? Hash::make($request->password) : $user->password,
                'is_active' => $request->is_active,
            ]);

            if ($emailChanged) {
                DB::table('sessions')->where('user_id', $user->id)->delete();
                // Jika pakai Passport atau Sanctum: $user->tokens()->delete();
            }

            DB::commit();

            return redirect()->route('ref.user.index');

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->withErrors(['confirm' => $th->getMessage()]);
        }
    }
    public function updatePosition(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'positionId' => 'required|exists:'.Position::class.',id',
        ]);

        DB::beginTransaction();

        try {
            $currentPosition = $user->currentPosition()->first();
            $positionId = $request->positionId;

            if ($currentPosition) {
                $user->positions()->updateExistingPivot($currentPosition->id, [
                    'end_date' => now(),
                    'is_active' => false
                ]);
            }

            $user->positions()->attach($positionId, [
                'start_date' => now(),
                'is_active' => true
            ]);

            DB::commit();

            return redirect()->route('ref.user.index');

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->withErrors(['confirm' => $th->getMessage()]);
        }
    }
    public function updateSpecialDisposition(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'position_ids' => 'nullable|array',
            'position_ids.*' => 'exists:'.Position::class.',id',
        ]);

        DB::beginTransaction();

        try {
           $user->updateSpecialDispositions($validated['position_ids']);

            DB::commit();

            return redirect()->route('ref.user.index');

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->withErrors(['confirm' => $th->getMessage()]);
        }
    }
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'roles' => 'nullable|array',
            'roles.*' => 'exists:'.Role::class.',id',
        ]);

        DB::beginTransaction();

        try {
            $user->syncRoles($validated['roles']);
            
            DB::commit();

            return redirect()->route('ref.user.index');

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->withErrors(['confirm' => $th->getMessage()]);
        }
    }
}

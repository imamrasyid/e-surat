<?php

use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function userCreationActor(): User
{
    $actor = User::factory()->create();
    $actor->givePermissionTo(Permission::findOrCreate('master.pengguna'));

    return $actor;
}

it('offers active positions and roles on the creation form', function () {
    $actor = userCreationActor();

    Position::create(['name' => 'Sekretaris', 'is_active' => true]);
    Position::create(['name' => 'Arsiparis', 'is_active' => true]);
    Position::create(['name' => 'Jabatan Nonaktif', 'is_active' => false]);
    $role = Role::findOrCreate('Pengelola');

    $this->actingAs($actor)
        ->get(route('ref.user.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('master/pengguna/Create')
            ->has('positions', 2)
            ->has('roles', 1)
            ->where('roles.0.id', $role->id));
});

it('creates a user with an active position and a role', function () {
    $actor = userCreationActor();

    $position = Position::create(['name' => 'Kepala Bagian Umum', 'is_active' => true]);
    $role = Role::findOrCreate('Pengelola');

    $this->actingAs($actor)
        ->post(route('ref.user.store'), [
            'name' => 'Rina Pengelola',
            'username' => 'rinapengelola',
            'position_id' => $position->id,
            'role_id' => $role->id,
        ])
        ->assertRedirect(route('ref.user.index'));

    $user = User::where('username', 'rinapengelola')->firstOrFail();

    expect($user->hasRole('Pengelola'))->toBeTrue()
        ->and($user->currentPosition()->value('positions.id'))->toBe($position->id)
        ->and($user->positions()->wherePivot('is_active', true)->count())->toBe(1)
        ->and(Hash::check('password', (string) $user->password))->toBeTrue();
});

it('requires an existing position and role when creating a user', function () {
    $actor = userCreationActor();

    $this->actingAs($actor)
        ->post(route('ref.user.store'), [
            'name' => 'Tanpa Jabatan',
            'username' => 'tanpajabatan',
        ])
        ->assertSessionHasErrors(['position_id', 'role_id']);

    $this->actingAs($actor)
        ->post(route('ref.user.store'), [
            'name' => 'Jabatan Palsu',
            'username' => 'jabatanpalsu',
            'position_id' => 9999,
            'role_id' => 9999,
        ])
        ->assertSessionHasErrors(['position_id', 'role_id']);

    expect(User::whereIn('username', ['tanpajabatan', 'jabatanpalsu'])->exists())->toBeFalse();
});

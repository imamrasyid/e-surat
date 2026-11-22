<?php

use App\Models\User;
use App\Services\PermissionsCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('forget clears the user permission cache key', function () {
    $user = User::factory()->create();

    Cache::put(PermissionsCache::key($user->id), ['memo.index'], now()->addMinutes(15));

    PermissionsCache::forget($user);

    expect(Cache::get(PermissionsCache::key($user->id)))->toBeNull();
});

it('forgetRole clears cached permissions of every user holding the role', function () {
    /** @var Role $role */
    $role = Role::create(['name' => 'operator']);
    $role->givePermissionTo(Permission::create(['name' => 'memo.index']));

    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $userA->assignRole($role);
    $userB->assignRole($role);

    Cache::put(PermissionsCache::key($userA->id), ['memo.index'], now()->addMinutes(15));
    Cache::put(PermissionsCache::key($userB->id), ['memo.index'], now()->addMinutes(15));

    PermissionsCache::forgetRole($role);

    expect(Cache::get(PermissionsCache::key($userA->id)))->toBeNull()
        ->and(Cache::get(PermissionsCache::key($userB->id)))->toBeNull();
});

it('clears the user permission cache when a user role is updated', function () {
    $actor = User::factory()->create();
    $actor->givePermissionTo(Permission::findOrCreate('master.pengguna'));

    $role = Role::findOrCreate('Pengelola');
    $target = User::factory()->create();

    Cache::put(PermissionsCache::key($target->id), ['memo.index'], now()->addMinutes(15));

    $this->actingAs($actor)
        ->post(route('ref.user.updateRole', $target), ['roles' => [$role->id]])
        ->assertRedirect(route('ref.user.index'));

    expect(Cache::get(PermissionsCache::key($target->id)))->toBeNull()
        ->and($target->fresh()->hasRole('Pengelola'))->toBeTrue();
});

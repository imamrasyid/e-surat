<?php

use App\Models\Position;
use App\Models\User;
use App\Services\InboxCountsCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

it('forgetPositions clears badge keys of users on affected positions', function () {
    $posTarget = Position::create(['name' => 'Target']);
    $posOther = Position::create(['name' => 'Other']);
    $user = User::factory()->create();
    $user->positions()->attach($posTarget->id, ['is_active' => true]);

    Cache::put(InboxCountsCache::key($user->id, $posTarget->id), ['primary' => 3], now()->addSeconds(30));
    Cache::put(InboxCountsCache::key($user->id, $posOther->id), ['primary' => 9], now()->addSeconds(30));

    InboxCountsCache::forgetPositions([$posTarget->id]);

    expect(Cache::get(InboxCountsCache::key($user->id, $posTarget->id)))->toBeNull()
        ->and(Cache::get(InboxCountsCache::key($user->id, $posOther->id)))->toEqual(['primary' => 9]);
});

it('forget clears current active position key', function () {
    $pos = Position::create(['name' => 'P']);
    $user = User::factory()->create();
    $user->positions()->attach($pos->id, ['is_active' => true]);

    Cache::put(InboxCountsCache::key($user->id, $pos->id), ['memo' => 2], now()->addSeconds(30));

    InboxCountsCache::forget($user);

    expect(Cache::get(InboxCountsCache::key($user->id, $pos->id)))->toBeNull();
});

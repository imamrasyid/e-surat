<?php

use App\Models\LetterRecipient;
use App\Models\User;
use App\Notifications\InboxItemNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('returns 404 for the telegram webhook when the feature is disabled', function () {
    config([
        'company.features' => [],
        'services.telegram.webhook_secret' => 'secret-token',
    ]);

    $this->postJson(
        route('telegram.webhook'),
        ['message' => ['chat' => ['id' => 123], 'text' => '/start token']],
        ['X-Telegram-Bot-Api-Secret-Token' => 'secret-token']
    )->assertNotFound();
});

it('reaches the webhook controller when the feature is enabled', function () {
    config([
        'company.features' => ['telegram'],
        'services.telegram.webhook_secret' => 'secret-token',
    ]);

    $this->postJson(
        route('telegram.webhook'),
        ['message' => ['chat' => ['id' => 123], 'text' => 'ping']],
        ['X-Telegram-Bot-Api-Secret-Token' => 'secret-token']
    )->assertOk()->assertJson(['ok' => true]);
});

it('returns 404 for telegram settings routes when the feature is disabled', function () {
    config(['company.features' => []]);

    $user = User::factory()->create();

    $this->actingAs($user)->post(route('telegram.link'))->assertNotFound();
    $this->actingAs($user)->get(route('telegram.status'))->assertNotFound();
    $this->actingAs($user)->delete(route('telegram.unlink'))->assertNotFound();
});

it('shares feature status with the frontend as a UX hint', function () {
    config(['company.features' => []]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('features.telegram', false)
            ->where('features.email', false)
        );

    config(['company.features' => ['telegram']]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('features.telegram', true)
        );
});

it('reports the feature status to the profile page', function () {
    config(['company.features' => ['telegram']]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/Profile')
            ->where('telegramEnabled', true)
        );
});

it('only includes the mail channel when the email feature is enabled', function () {
    $user = User::factory()->create();
    $notification = new InboxItemNotification(new LetterRecipient);

    config(['company.features' => []]);
    expect($notification->via($user))->toBe([]);

    config(['company.features' => ['email']]);
    expect($notification->via($user))->toBe(['mail']);
});

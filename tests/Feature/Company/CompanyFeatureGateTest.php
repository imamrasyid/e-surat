<?php

use App\Models\LetterRecipient;
use App\Models\User;
use App\Notifications\InboxItemNotification;
use App\Services\CompanyFeature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use NotificationChannels\WebPush\WebPushChannel;

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
    expect($notification->via($user))->toBe(['database']);

    config(['company.features' => ['email']]);
    expect($notification->via($user))->toBe(['database', 'mail']);
});

/**
 * @param  array<string, mixed>  $overrides
 */
function eligibleWebPushConfig(array $overrides = []): void
{
    config(array_merge([
        'company.features' => ['webpush'],
        'app.url' => 'https://esurat.company-a.example.test',
        'webpush.vapid.subject' => 'mailto:ops@company-a.test',
        'webpush.vapid.public_key' => 'public-key',
        'webpush.vapid.private_key' => 'private-key',
    ], $overrides));
}

it('enables web push only for an https dns host with complete vapid keys', function () {
    eligibleWebPushConfig();

    expect(app(CompanyFeature::class)->webPushEligible())->toBeTrue();
});

it('disables web push for http and ip app urls', function () {
    eligibleWebPushConfig(['app.url' => 'http://esurat.company-a.example.test']);
    expect(app(CompanyFeature::class)->webPushEligible())->toBeFalse();

    eligibleWebPushConfig(['app.url' => 'https://203.0.113.10']);
    expect(app(CompanyFeature::class)->webPushEligible())->toBeFalse();

    eligibleWebPushConfig(['app.url' => 'https://[::1]']);
    expect(app(CompanyFeature::class)->webPushEligible())->toBeFalse();
});

it('disables web push for a malformed app url', function () {
    eligibleWebPushConfig(['app.url' => 'not a url']);

    expect(app(CompanyFeature::class)->webPushEligible())->toBeFalse();
});

it('disables web push without complete vapid keys', function () {
    eligibleWebPushConfig(['webpush.vapid.private_key' => null]);

    expect(app(CompanyFeature::class)->webPushEligible())->toBeFalse();
});

it('selects the web push channel only for an eligible tenant with a subscription', function () {
    $user = User::factory()->create();
    $notification = new InboxItemNotification(new LetterRecipient);

    eligibleWebPushConfig();
    expect($notification->via($user))->toBe(['database']);

    $user->updatePushSubscription('https://push.example.test/abc', 'p256dh', 'auth', 'aes128gcm');

    expect($notification->via($user))->toBe(['database', WebPushChannel::class]);
});

it('keeps database notifications when web push is ineligible', function () {
    $user = User::factory()->create();
    $user->updatePushSubscription('https://push.example.test/abc', 'p256dh', 'auth', 'aes128gcm');

    $notification = new InboxItemNotification(new LetterRecipient);

    eligibleWebPushConfig(['app.url' => 'https://198.51.100.7']);

    expect($notification->via($user))->toBe(['database']);
});

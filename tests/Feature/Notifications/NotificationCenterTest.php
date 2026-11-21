<?php

use App\Models\User;
use App\Notifications\InboxItemNotification;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function notificationFor(User $user, array $data = [], ?CarbonImmutable $readAt = null, ?CarbonImmutable $createdAt = null): string
{
    $id = (string) Str::uuid();

    DB::table('notifications')->insert([
        'id' => $id,
        'type' => InboxItemNotification::class,
        'notifiable_type' => $user->getMorphClass(),
        'notifiable_id' => $user->id,
        'data' => json_encode(array_merge([
            'title' => 'Surat masuk primary baru',
            'body' => 'Undangan Rapat Koordinasi',
            'url' => '/primary/1/letter-1',
            'item_type' => 'letter',
            'security_classification' => 'B',
            'received_via' => 'other',
        ], $data)),
        'read_at' => $readAt,
        'created_at' => $createdAt ?? now(),
        'updated_at' => $createdAt ?? now(),
    ]);

    return $id;
}

/**
 * @param  array<string, mixed>  $overrides
 */
function eligibleNotificationWebPush(array $overrides = []): void
{
    config(array_merge([
        'company.features' => ['webpush'],
        'app.url' => 'https://esurat.company-a.example.test',
        'webpush.vapid.subject' => 'mailto:ops@company-a.test',
        'webpush.vapid.public_key' => 'public-key',
        'webpush.vapid.private_key' => 'private-key',
    ], $overrides));
}

it('shows only the current user notification history with unread count', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $oldId = notificationFor($user, ['title' => 'Lama'], createdAt: now()->subMinute());
    notificationFor($user, ['title' => 'Baru'], readAt: now());
    notificationFor($other);

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('notifications/Index')
            ->has('notifications.data', 2)
            ->where('notifications.total', 2)
            ->where('notificationCenter.unread_count', 1)
            ->has('notificationCenter.latest', 1)
            ->where('notificationCenter.latest.0.id', $oldId)
            ->where('notificationCenter.latest.0.item_type', 'primary')
            ->where('notifications.data.0.title', 'Baru')
            ->where('notifications.data.0.security_classification', 'B')
            ->where('notifications.data.0.received_via', 'other')
        );
});

it('marks only the current user notification as read', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $ownId = notificationFor($user);
    $otherId = notificationFor($other);

    $this->actingAs($user)
        ->patch(route('notifications.read', $ownId))
        ->assertRedirect('/primary/1/letter-1');

    expect(DatabaseNotification::find($ownId)->read_at)->not->toBeNull()
        ->and(DatabaseNotification::find($otherId)->read_at)->toBeNull();

    $this->actingAs($user)
        ->patch(route('notifications.read', $otherId))
        ->assertNotFound();
});

it('cannot open another user notification from the external link', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $otherId = notificationFor($other);

    $this->actingAs($user)
        ->get(route('notifications.open', $otherId))
        ->assertNotFound();
});

it('requires authentication for the external notification link', function () {
    $user = User::factory()->create();

    $id = notificationFor($user);

    $this->get(route('notifications.open', $id))
        ->assertRedirect(route('login'));
});

it('cannot delete another user notification', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $ownId = notificationFor($user);
    $otherId = notificationFor($other);

    $this->actingAs($user)
        ->delete(route('notifications.destroy', $otherId))
        ->assertNotFound();

    expect(DatabaseNotification::find($otherId))->not->toBeNull();

    $this->actingAs($user)
        ->delete(route('notifications.destroy', $ownId))
        ->assertRedirect();

    expect(DatabaseNotification::find($ownId))->toBeNull();
});

it('deletes all notifications only for the current user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    notificationFor($user);
    notificationFor($user, readAt: now());
    notificationFor($other);

    $this->actingAs($user)
        ->delete(route('notifications.destroyAll'))
        ->assertRedirect();

    expect($user->notifications()->count())->toBe(0)
        ->and($other->notifications()->count())->toBe(1);
});

it('shares the notification center summary with authenticated pages', function () {
    $user = User::factory()->create();

    foreach (range(1, 12) as $i) {
        notificationFor($user, ['title' => 'Notif '.$i], createdAt: now()->addSeconds($i));
    }

    config(['company.features' => []]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertInertia(fn (Assert $page) => $page
            ->where('notificationCenter.unread_count', 12)
            ->has('notificationCenter.latest', 10)
            ->where('notificationCenter.web_push.enabled', false)
            ->where('notificationCenter.web_push.public_key', null)
        );

    eligibleNotificationWebPush();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertInertia(fn (Assert $page) => $page
            ->where('notificationCenter.web_push.enabled', true)
            ->where('notificationCenter.web_push.public_key', 'public-key')
        );
});

it('stores and removes the current user push subscription', function () {
    eligibleNotificationWebPush();

    $user = User::factory()->create();

    $payload = [
        'endpoint' => 'https://push.example.test/abc',
        'keys' => ['p256dh' => 'p256dh-key', 'auth' => 'auth-token'],
        'content_encoding' => 'aes128gcm',
    ];

    $this->actingAs($user)
        ->post(route('notifications.push-subscription.store'), $payload)
        ->assertRedirect();

    expect($user->pushSubscriptions()->where('endpoint', $payload['endpoint'])->exists())->toBeTrue();

    $this->actingAs($user)
        ->delete(route('notifications.push-subscription.destroy'), ['endpoint' => $payload['endpoint']])
        ->assertRedirect();

    expect($user->pushSubscriptions()->where('endpoint', $payload['endpoint'])->exists())->toBeFalse();
});

it('rejects push subscriptions when web push is disabled', function () {
    config(['company.features' => []]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('notifications.push-subscription.store'), [
            'endpoint' => 'https://push.example.test/abc',
            'keys' => ['p256dh' => 'p256dh-key', 'auth' => 'auth-token'],
        ])
        ->assertNotFound();

    $this->actingAs($user)
        ->delete(route('notifications.push-subscription.destroy'), ['endpoint' => 'https://push.example.test/abc'])
        ->assertNotFound();
});

it('validates the push subscription payload', function () {
    eligibleNotificationWebPush();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('notifications.push-subscription.store'), [])
        ->assertSessionHasErrors(['endpoint', 'keys.p256dh', 'keys.auth']);
});

it('rejects non-https and internal push endpoints', function (string $endpoint) {
    eligibleNotificationWebPush();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('notifications.push-subscription.store'), [
            'endpoint' => $endpoint,
            'keys' => ['p256dh' => 'p256dh-key', 'auth' => 'auth-token'],
        ])
        ->assertSessionHasErrors('endpoint');

    expect($user->pushSubscriptions()->count())->toBe(0);
})->with([
    'http metadata ip' => ['http://169.254.169.254/latest/meta-data/'],
    'https metadata ip' => ['https://169.254.169.254/latest/meta-data/'],
    'ipv6 loopback' => ['https://[::1]/push'],
    'localhost' => ['https://localhost/push'],
    'internal suffix' => ['https://service.internal/push'],
]);

it('marks all current user notifications as read', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    notificationFor($user);
    notificationFor($user, readAt: now());
    notificationFor($other);

    $this->actingAs($user)
        ->patch(route('notifications.readAll'))
        ->assertRedirect();

    expect($user->notifications()->whereNull('read_at')->count())->toBe(0)
        ->and($user->notifications()->count())->toBe(2)
        ->and($other->unreadNotifications()->count())->toBe(1);
});

it('paginates the history at fifteen rows per page', function () {
    $user = User::factory()->create();

    foreach (range(1, 16) as $i) {
        notificationFor($user, ['title' => 'Notif '.$i], createdAt: now()->addSeconds($i));
    }

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('notifications.per_page', 15)
            ->where('notifications.total', 16)
            ->where('notifications.last_page', 2)
            ->where('notifications.current_page', 1)
            ->has('notifications.data', 15)
        );

    $this->actingAs($user)
        ->get(route('notifications.index', ['page' => 2]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('notifications.current_page', 2)
            ->has('notifications.data', 1)
        );
});

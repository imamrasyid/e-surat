<?php

use App\Actions\Telegram\CreateTelegramLinkTokenAction;
use App\Models\TelegramLinkToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

// Feature gate harus aktif agar route Telegram dapat diakses.
beforeEach(fn () => config(['company.features' => ['telegram']]));

function telegramSettingsConfig(): void
{
    config([
        'services.telegram.bot_token' => 'test-token',
        'services.telegram.bot_username' => 'esurat_bot',
        'services.telegram.webhook_secret' => 'secret-token',
    ]);
}

function telegramSettingsUser(?string $chatId = null): User
{
    $user = User::factory()->create();

    if ($chatId !== null) {
        $user->telegram_chat_id = $chatId;
        $user->save();
    }

    return $user;
}

/**
 * @param  array<string, mixed>  $message
 */
function postTelegramWebhook(object $test, array $message, ?string $secret = 'secret-token'): TestResponse
{
    $headers = $secret === null ? [] : ['X-Telegram-Bot-Api-Secret-Token' => $secret];

    return $test->postJson(route('telegram.webhook'), ['message' => $message], $headers);
}

it('returns a pairing link with qr svg and stores only the token hash', function () {
    telegramSettingsConfig();

    $user = telegramSettingsUser();

    $response = $this->actingAs($user)->post(route('telegram.link'));

    $response->assertOk()->assertJsonStructure(['url', 'svg']);

    $url = (string) $response->json('url');
    $rawToken = Str::afterLast($url, 'start=');
    $svg = (string) $response->json('svg');

    expect($url)->toStartWith('https://t.me/esurat_bot?start=')
        ->and(strlen($rawToken))->toBe(64)
        ->and($svg)->toStartWith('<svg');

    $token = TelegramLinkToken::sole();

    expect($token->token_hash)->toBe(hash('sha256', $rawToken))
        ->and($token->used_at)->toBeNull()
        ->and($token->user_id)->toBe($user->id);

    $this->assertDatabaseMissing('telegram_link_tokens', ['token_hash' => $rawToken]);
});

it('strips the at sign from the configured bot username', function () {
    telegramSettingsConfig();
    config(['services.telegram.bot_username' => '@esurat_bot']);

    $user = telegramSettingsUser();

    $response = $this->actingAs($user)->post(route('telegram.link'));

    expect((string) $response->json('url'))->toStartWith('https://t.me/esurat_bot?start=');
});

it('does not create a token when the bot is not configured', function () {
    config([
        'services.telegram.bot_token' => null,
        'services.telegram.bot_username' => null,
    ]);

    $user = telegramSettingsUser();

    $this->actingAs($user)
        ->post(route('telegram.link'))
        ->assertStatus(422)
        ->assertJsonPath('errors.telegram', fn (string $message) => filled($message));

    expect(TelegramLinkToken::count())->toBe(0);
});

it('reports the telegram connection status as json', function () {
    $connected = telegramSettingsUser('123456789');
    $disconnected = telegramSettingsUser();

    $this->actingAs($connected)->getJson(route('telegram.status'))->assertOk()->assertJson(['connected' => true]);
    $this->actingAs($disconnected)->getJson(route('telegram.status'))->assertOk()->assertJson(['connected' => false]);
});

it('exposes the telegram state on the profile page', function () {
    $connected = telegramSettingsUser('123456789');
    $disconnected = telegramSettingsUser();

    $this->actingAs($connected)
        ->get(route('profile.edit'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/Profile')
            ->where('telegramConnected', true)
            ->missing('telegramLinkUrl'));

    $this->actingAs($disconnected)
        ->get(route('profile.edit'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('telegramConnected', false));
});

it('rejects webhook calls without the configured secret', function () {
    telegramSettingsConfig();

    $user = telegramSettingsUser();
    $rawToken = app(CreateTelegramLinkTokenAction::class)->handle($user);

    postTelegramWebhook($this, ['chat' => ['id' => 4242], 'text' => '/start '.$rawToken], null)
        ->assertForbidden();

    postTelegramWebhook($this, ['chat' => ['id' => 4242], 'text' => '/start '.$rawToken], 'wrong')
        ->assertForbidden();

    expect($user->refresh()->telegram_chat_id)->toBeNull();
});

it('links the chat id when a valid start token arrives', function () {
    telegramSettingsConfig();
    Http::fake(['api.telegram.org/bot*/sendMessage' => Http::response(['ok' => true])]);

    $user = telegramSettingsUser();
    $rawToken = app(CreateTelegramLinkTokenAction::class)->handle($user);

    postTelegramWebhook($this, ['chat' => ['id' => 55555], 'text' => '/start '.$rawToken])
        ->assertOk();

    expect($user->refresh()->telegram_chat_id)->toBe('55555');

    $token = TelegramLinkToken::sole();

    expect($token->used_at)->not->toBeNull();

    Http::assertSent(fn ($request) => $request['chat_id'] === '55555'
        && str_contains($request['text'], 'berhasil terhubung'));
});

it('ignores updates that are not start commands', function () {
    telegramSettingsConfig();
    Http::fake();

    $user = telegramSettingsUser();
    $rawToken = app(CreateTelegramLinkTokenAction::class)->handle($user);

    postTelegramWebhook($this, ['chat' => ['id' => 66666], 'text' => 'halo'])->assertOk();
    postTelegramWebhook($this, ['chat' => ['id' => 66666], 'text' => '/start'])->assertOk();

    expect($user->refresh()->telegram_chat_id)->toBeNull()
        ->and(TelegramLinkToken::sole()->used_at)->toBeNull();

    Http::assertNothingSent();
});

it('refuses an expired or already used token', function () {
    telegramSettingsConfig();
    Http::fake(['api.telegram.org/bot*/sendMessage' => Http::response(['ok' => true])]);

    $user = telegramSettingsUser();
    $rawToken = app(CreateTelegramLinkTokenAction::class)->handle($user);

    TelegramLinkToken::sole()->update(['expires_at' => now()->subMinute()]);

    postTelegramWebhook($this, ['chat' => ['id' => 77777], 'text' => '/start '.$rawToken])->assertOk();

    expect($user->refresh()->telegram_chat_id)->toBeNull();

    $rawToken = app(CreateTelegramLinkTokenAction::class)->handle($user);
    TelegramLinkToken::sole()->update(['used_at' => now()]);

    postTelegramWebhook($this, ['chat' => ['id' => 77777], 'text' => '/start '.$rawToken])->assertOk();

    expect($user->refresh()->telegram_chat_id)->toBeNull();
});

it('does not overwrite a chat id already owned by another user', function () {
    telegramSettingsConfig();
    Http::fake(['api.telegram.org/bot*/sendMessage' => Http::response(['ok' => true])]);

    telegramSettingsUser('88888');

    $user = telegramSettingsUser();
    $rawToken = app(CreateTelegramLinkTokenAction::class)->handle($user);

    postTelegramWebhook($this, ['chat' => ['id' => 88888], 'text' => '/start '.$rawToken])->assertOk();

    expect($user->refresh()->telegram_chat_id)->toBeNull()
        ->and(TelegramLinkToken::sole()->used_at)->toBeNull();

    Http::assertSent(fn ($request) => str_contains($request['text'], 'sudah terhubung dengan akun e-Surat lain'));
});

it('removes the chat id when the telegram link is revoked', function () {
    $user = telegramSettingsUser('99999');

    $this->actingAs($user)
        ->delete(route('telegram.unlink'))
        ->assertRedirect(route('profile.edit'));

    expect($user->refresh()->telegram_chat_id)->toBeNull();
});

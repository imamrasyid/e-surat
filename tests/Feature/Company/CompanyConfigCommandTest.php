<?php

/**
 * @param  array<string, mixed>  $overrides
 */
function baseCompanyConfig(array $overrides = []): void
{
    config(array_merge([
        'company.key' => 'company-a',
        'company.features' => [],
        'company.known_features' => ['email', 'telegram', 'webpush'],
        'company.feature_requirements' => [
            'email' => [
                'required' => ['mail.from.address'],
                'not_in' => ['mail.default' => ['log', 'array']],
            ],
            'telegram' => [
                'required' => [
                    'services.telegram.bot_token',
                    'services.telegram.bot_username',
                    'services.telegram.webhook_secret',
                ],
            ],
            'webpush' => [
                'required' => [
                    'webpush.vapid.subject',
                    'webpush.vapid.public_key',
                    'webpush.vapid.private_key',
                ],
            ],
        ],
        'company.ruleset' => 'default',
        'company.rulesets' => ['default'],
    ], $overrides));
}

it('passes for a known configuration without optional features', function () {
    baseCompanyConfig();

    $this->artisan('company:validate-config')
        ->expectsOutputToContain('Konfigurasi company valid')
        ->assertSuccessful();
});

it('fails when the company key is missing', function () {
    baseCompanyConfig(['company.key' => null]);

    $this->artisan('company:validate-config')
        ->expectsOutputToContain('COMPANY_KEY wajib diisi')
        ->assertExitCode(1);
});

it('rejects an unregistered ruleset', function () {
    baseCompanyConfig(['company.ruleset' => 'custom-rules']);

    $this->artisan('company:validate-config')
        ->expectsOutputToContain('COMPANY_RULESET [custom-rules] tidak dikenal')
        ->assertExitCode(1);
});

it('rejects a feature that is not known', function () {
    baseCompanyConfig(['company.features' => ['telegram', 'unknown-feature']]);

    $this->artisan('company:validate-config')
        ->expectsOutputToContain('feature tidak dikenal [unknown-feature]')
        ->assertExitCode(1);
});

it('rejects email notifications while the mailer is only a log', function () {
    baseCompanyConfig(['company.features' => ['email']]);
    config([
        'mail.default' => 'log',
        'mail.from.address' => 'no-reply@company-a.test',
    ]);

    $this->artisan('company:validate-config')
        ->expectsOutputToContain('mail.default bernilai [log]')
        ->assertExitCode(1);
});

it('requires a sender address when email notifications are active', function () {
    baseCompanyConfig(['company.features' => ['email']]);
    config([
        'mail.default' => 'smtp',
        'mail.from.address' => null,
    ]);

    $this->artisan('company:validate-config')
        ->expectsOutputToContain('mail.from.address belum diisi')
        ->assertExitCode(1);
});

it('passes for active email notifications with a real mailer and sender', function () {
    baseCompanyConfig(['company.features' => ['email']]);
    config([
        'mail.default' => 'smtp',
        'mail.from.address' => 'no-reply@company-a.test',
    ]);

    $this->artisan('company:validate-config')
        ->expectsOutputToContain('features=email')
        ->assertSuccessful();
});

it('requires the telegram credentials when the feature is active', function () {
    baseCompanyConfig(['company.features' => ['telegram']]);
    config([
        'services.telegram.bot_token' => null,
        'services.telegram.bot_username' => null,
        'services.telegram.webhook_secret' => null,
    ]);

    $this->artisan('company:validate-config')
        ->expectsOutputToContain('services.telegram.bot_token belum diisi')
        ->assertExitCode(1);
});

it('passes for an active telegram feature with complete credentials', function () {
    baseCompanyConfig(['company.features' => ['telegram']]);
    config([
        'services.telegram.bot_token' => 'token',
        'services.telegram.bot_username' => 'esurat_bot',
        'services.telegram.webhook_secret' => 'secret',
    ]);

    $this->artisan('company:validate-config')
        ->expectsOutputToContain('features=telegram')
        ->assertSuccessful();
});

it('requires the vapid keys when webpush is active', function () {
    baseCompanyConfig(['company.features' => ['webpush']]);
    config([
        'webpush.vapid.subject' => null,
        'webpush.vapid.public_key' => null,
        'webpush.vapid.private_key' => null,
    ]);

    $this->artisan('company:validate-config')
        ->expectsOutputToContain('webpush.vapid.subject belum diisi')
        ->assertExitCode(1);
});

it('passes for an active webpush feature with complete vapid keys', function () {
    baseCompanyConfig(['company.features' => ['webpush']]);
    config([
        'webpush.vapid.subject' => 'mailto:ops@company-a.test',
        'webpush.vapid.public_key' => 'public-key',
        'webpush.vapid.private_key' => 'private-key',
    ]);

    $this->artisan('company:validate-config')
        ->expectsOutputToContain('features=webpush')
        ->assertSuccessful();
});

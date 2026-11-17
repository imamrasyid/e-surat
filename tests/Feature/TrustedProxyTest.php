<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function () {
    Route::get('/__proxy-probe', fn (Request $request) => [
        'ip' => $request->ip(),
        'scheme' => $request->getScheme(),
    ]);
});

/**
 * Simulasi proxy di host (mis. Caddy/Nginx) yang meneruskan header standar.
 */
function proxyProbe(string $remoteAddr, array $headers = []): array
{
    $request = Request::create('http://esurat.example.test/__proxy-probe', 'GET');
    $request->server->set('REMOTE_ADDR', $remoteAddr);

    foreach ($headers as $name => $value) {
        $request->headers->set($name, $value);
    }

    $response = app()->handle($request);

    return (array) json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
}

it('trusts only loopback proxies by default', function () {
    $result = proxyProbe('172.18.0.1', [
        'X-Forwarded-For' => '203.0.113.7',
        'X-Forwarded-Proto' => 'https',
    ]);

    expect($result['ip'])->toBe('172.18.0.1')
        ->and($result['scheme'])->toBe('http');
});

it('trusts a configured docker bridge proxy range and applies forwarded headers', function () {
    config(['trustedproxy.proxies' => '127.0.0.1,::1,172.16.0.0/12']);

    $result = proxyProbe('172.18.0.1', [
        'X-Forwarded-For' => '203.0.0.7',
        'X-Forwarded-Proto' => 'https',
    ]);

    expect($result['ip'])->toBe('203.0.0.7')
        ->and($result['scheme'])->toBe('https');
});

it('does not apply forwarded headers from a client outside the trusted range', function () {
    config(['trustedproxy.proxies' => '127.0.0.1,::1,172.16.0.0/12']);

    $result = proxyProbe('198.51.100.9', [
        'X-Forwarded-For' => '10.9.9.9',
        'X-Forwarded-Proto' => 'https',
    ]);

    expect($result['ip'])->toBe('198.51.100.9')
        ->and($result['scheme'])->toBe('http');
});

<?php

use App\Http\Middleware\WildcardPermissionMiddleware;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpKernel\Exception\HttpException;

uses(RefreshDatabase::class);

function wildcardRequestFor(User $user): Request
{
    $request = Request::create('/master-data', 'GET');
    $request->setUserResolver(fn () => $user);

    return $request;
}

function wildcardNext(): Closure
{
    return fn () => response('next');
}

it('passes a user whose permission covers the wildcard pattern', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::findOrCreate('master.pengguna'));

    $middleware = new WildcardPermissionMiddleware;
    $response = $middleware->handle(wildcardRequestFor($user), wildcardNext(), 'master.*');

    expect($response->getStatusCode())->toBe(200);
});

it('rejects a user with no permission under the wildcard prefix', function () {
    $user = User::factory()->create();

    $middleware = new WildcardPermissionMiddleware;

    $this->expectException(HttpException::class);

    $middleware->handle(wildcardRequestFor($user), wildcardNext(), 'master.*');
});

it('accepts an exact permission match without wildcard', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::findOrCreate('sent.index'));

    $middleware = new WildcardPermissionMiddleware;
    $response = $middleware->handle(wildcardRequestFor($user), wildcardNext(), 'sent.index');

    expect($response->getStatusCode())->toBe(200);
});

it('checks the first matching of several pipe separated patterns', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::findOrCreate('master.jabatan'));

    $middleware = new WildcardPermissionMiddleware;
    $response = $middleware->handle(wildcardRequestFor($user), wildcardNext(), 'master.pengguna|master.*');

    expect($response->getStatusCode())->toBe(200);
});

it('never grants the wildcard to a prefix that does not match', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::findOrCreate('master.pengguna'));

    $middleware = new WildcardPermissionMiddleware;

    $this->expectException(HttpException::class);

    $middleware->handle(wildcardRequestFor($user), wildcardNext(), 'sent.*');
});

<?php

use App\Models\Followup;
use App\Models\Position;
use App\Models\RefKdKlasifikasi;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('seeds permissions, shared master data, and the default administrator', function () {
    $this->seed(DatabaseSeeder::class);

    $admin = User::where('username', 'superadmin')->sole();

    expect($admin->name)->toBe('Super Administrator')
        ->and($admin->email)->toBeNull()
        ->and($admin->is_active)->toBeTrue()
        ->and(Hash::check('password', $admin->password))->toBeTrue()
        ->and($admin->email_verified_at)->not->toBeNull()
        ->and($admin->hasRole('Super Admin'))->toBeTrue();

    expect(Position::count())->toBe(6)
        ->and(RefKdKlasifikasi::count())->toBe(6)
        ->and(Followup::count())->toBe(4);

    expect(Followup::pluck('name')->all())->toEqualCanonicalizing([
        'Diperiksa dan Diproses Lebih Lanjut',
        'Dievaluasi dan Dilaporkan',
        'Diteliti/Dikaji',
        'Diberikan Saran/Pendapat',
    ]);

    $pengelola = Role::findByName('Pengelola');
    $pengguna = Role::findByName('Pengguna');

    expect($pengelola->hasPermissionTo('sent.index'))->toBeTrue()
        ->and($pengelola->hasPermissionTo('primary.index'))->toBeFalse()
        ->and($pengguna->hasPermissionTo('primary.index'))->toBeTrue()
        ->and($pengguna->hasPermissionTo('sent.index'))->toBeFalse();
});

it('is idempotent and does not reset a changed administrator password', function () {
    $this->seed(DatabaseSeeder::class);

    $admin = User::where('username', 'superadmin')->sole();
    $admin->forceFill(['password' => 'password-baru'])->save();

    $this->seed(DatabaseSeeder::class);

    expect(User::count())->toBe(1)
        ->and(Position::count())->toBe(6)
        ->and(Followup::count())->toBe(4)
        ->and(RefKdKlasifikasi::count())->toBe(6)
        ->and(Hash::check('password-baru', $admin->refresh()->password))->toBeTrue();
});

it('does not create demo accounts', function () {
    $this->seed(DatabaseSeeder::class);

    expect(User::where('email', 'pengelola@e-surat.test')->exists())->toBeFalse()
        ->and(User::where('email', 'msbhdn@icloud.com')->exists())->toBeFalse();
});

it('does not overwrite master data adjusted through the UI', function () {
    $this->seed(DatabaseSeeder::class);

    $pengelola = Role::findByName('Pengelola');
    $pengelola->revokePermissionTo('sent.index');

    Position::where('name', 'Pimpinan')->update(['is_active' => false]);
    Followup::where('name', 'Diteliti/Dikaji')->update(['is_active' => false]);

    $this->seed(DatabaseSeeder::class);

    expect($pengelola->refresh()->hasPermissionTo('sent.index'))->toBeFalse()
        ->and((bool) Position::where('name', 'Pimpinan')->value('is_active'))->toBeFalse()
        ->and((bool) Followup::where('name', 'Diteliti/Dikaji')->value('is_active'))->toBeFalse();
});

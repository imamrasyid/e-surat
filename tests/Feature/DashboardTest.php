<?php

use App\Models\Letter;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function makeDashboardPageLetter(int $createdBy, array $attributes = []): Letter
{
    return Letter::create([
        'letter_id' => (string) Str::ulid(),
        'sender' => 'Instansi Pengirim',
        'number' => '002/ST/2026',
        'date' => now()->toDateString(),
        'receipt_date' => now()->toDateString(),
        'subject' => 'Surat Uji Halaman Dashboard',
        'category' => '1',
        'agenda' => '002',
        'type' => 'in',
        'status' => 'open',
        'created_by' => $createdBy,
        ...$attributes,
    ]);
}

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('Super Admin sees the admin dashboard without a chart series', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('Super Admin'));

    User::factory()->create(['is_active' => true]);
    User::factory()->create(['is_active' => false]);
    Position::create(['name' => 'Sekretaris', 'is_active' => true]);
    makeDashboardPageLetter($admin->id, ['type' => 'in', 'status' => 'open']);

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('dashboard.role', 'admin')
            ->where('dashboard.stats.activeUsers', 2)
            ->where('dashboard.stats.activePositions', 1)
            ->where('dashboard.stats.totalLetters', 1)
            ->where('dashboard.stats.openLetters', 1)
            ->where('chartData', [])
            ->where('userChartData', null)
        );
});

test('Pengelola sees the manager dashboard with a 90-day chart series', function () {
    $manager = User::factory()->create();
    $manager->assignRole(Role::findOrCreate('Pengelola'));

    makeDashboardPageLetter($manager->id, ['type' => 'in', 'status' => 'open']);
    makeDashboardPageLetter($manager->id, ['type' => 'out', 'status' => 'close']);

    $this->actingAs($manager)
        ->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('dashboard.role', 'manager')
            ->where('dashboard.stats.totalIncomingLetters', 1)
            ->where('dashboard.stats.openLetters', 1)
            ->where('dashboard.stats.inProcessLetters', 0)
            ->where('dashboard.stats.completedIncomingLetters', 0)
            ->has('chartData', 90)
            ->where('userChartData', null)
        );
});

test('Pengguna sees the user dashboard without a chart series', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('dashboard.role', 'user')
            ->where('dashboard.stats', null)
            ->where('chartData', [])
            ->has('userChartData.series', 90)
            ->where('userChartData.totals', ['primary' => 0, 'undangan' => 0, 'disposisi' => 0, 'memo' => 0])
            ->where('userChartData.totalReceived', 0)
        );
});

test('Pengguna queue chart endpoint returns the requested range', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson('/dashboard/user-chart-data?days=7')
        ->assertOk()
        ->assertJsonCount(7, 'series')
        ->assertJsonPath('totals.memo', 0)
        ->assertJsonPath('totalReceived', 0);

    $this->actingAs($user)
        ->getJson('/dashboard/user-chart-data?days=5')
        ->assertStatus(422);
});

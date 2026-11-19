<?php

use App\Models\Letter;
use App\Models\LetterActivity;
use App\Models\LetterRecipient;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

function tndeDetailUserOn(Position $position, string ...$permissions): User
{
    $user = User::factory()->create();
    $user->positions()->sync([$position->id => ['is_active' => true]]);

    foreach ($permissions as $permission) {
        $user->givePermissionTo(Permission::findOrCreate($permission));
    }

    return $user;
}

/**
 * @return array{0: Letter, 1: LetterRecipient, 2: Position}
 */
function tndeDetailLetter(string $category, User $registrar): array
{
    $targetPosition = Position::create(['name' => 'Kepala Bagian Umum', 'is_active' => true]);

    $letter = Letter::create([
        'letter_id' => (string) Str::ulid(),
        'sender' => 'Sekretariat Daerah',
        'number' => '005/1234/2026',
        'date' => '2026-11-01',
        'receipt_date' => '2026-11-02',
        'subject' => 'Undangan Rapat Koordinasi TNDE',
        'category' => $category,
        'security_classification' => 'T',
        'received_via' => 'email',
        'agenda' => 'UND-2026-001',
        'type' => 'in',
        'status' => 'open',
        'created_by' => $registrar->id,
    ]);

    $recipient = $letter->recipients()->create([
        'from_user_id' => $registrar->id,
        'from_position_id' => $targetPosition->id,
        'to_position_id' => $targetPosition->id,
        'received_at' => now(),
    ]);

    LetterActivity::create([
        'letter_id' => $letter->id,
        'user_id' => $registrar->id,
        'action' => 'register_incoming',
        'description' => 'Mendaftarkan surat masuk',
    ]);

    return [$letter, $recipient, $targetPosition];
}

it('returns invitation metadata and audit history in the detail props', function () {
    $registrar = User::factory()->create();

    [$letter, $recipient, $targetPosition] = tndeDetailLetter('2', $registrar);
    $reader = tndeDetailUserOn($targetPosition, 'undangan.show');

    $this->actingAs($reader)
        ->get(route('inbox.undangan.show', [
            'recipient' => $recipient->id,
            'letter_id' => $letter->letter_id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('app/undangan/Show')
            ->where('letter.security_classification', 'T')
            ->where('letter.received_via', 'email')
            ->has('letter.activities', 1)
            ->where('letter.activities.0.action', 'register_incoming')
            ->where('letter.activities.0.description', 'Mendaftarkan surat masuk')
            ->where('letter.activities.0.user.name', $registrar->name)
        );
});

it('returns the same detail contract for primary incoming letters', function () {
    $registrar = User::factory()->create();

    [$letter, $recipient, $targetPosition] = tndeDetailLetter('1', $registrar);
    $reader = tndeDetailUserOn($targetPosition, 'primary.show');

    $this->actingAs($reader)
        ->get(route('inbox.primary.show', [
            'recipient' => $recipient->id,
            'letter_id' => $letter->letter_id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('app/primary/Show')
            ->where('letter.security_classification', 'T')
            ->where('letter.received_via', 'email')
            ->has('letter.activities', 1)
            ->where('letter.activities.0.description', 'Mendaftarkan surat masuk')
            ->where('letter.activities.0.user.name', $registrar->name)
        );
});

it('redirects a user from an unrelated position away from an invitation', function () {
    $registrar = User::factory()->create();

    [$letter, $recipient] = tndeDetailLetter('2', $registrar);

    $outsiderPosition = Position::create(['name' => 'Bagian Lain', 'is_active' => true]);
    $outsider = tndeDetailUserOn($outsiderPosition, 'undangan.show');

    $this->actingAs($outsider)
        ->get(route('inbox.undangan.show', [
            'recipient' => $recipient->id,
            'letter_id' => $letter->letter_id,
        ]))
        ->assertRedirect()
        ->assertSessionHas(
            'inertia.flash_data',
            fn (array $flash) => $flash['toast']['type'] === 'error'
                && $flash['toast']['message'] === 'Anda tidak memiliki akses ke surat ini.',
        );
});

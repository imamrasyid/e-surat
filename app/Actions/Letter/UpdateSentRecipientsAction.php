<?php

namespace App\Actions\Letter;

use App\Models\Letter;
use App\Models\LetterRecipient;
use App\Models\Position;
use App\Models\User;
use App\Services\LetterActivityService;
use Illuminate\Support\Facades\DB;

class UpdateSentRecipientsAction
{
    /**
     * Update recipients of a sent letter and log modifications.
     *
     * @param  array<int, array{id?: int|string|null, to_position_id: int|string, instruction?: string|null}>  $recipients
     */
    public function handle(
        Letter $letter,
        User $user,
        ?Position $currentPosition,
        array $recipients
    ): void {
        DB::transaction(function () use ($recipients, $letter, $user, $currentPosition): void {
            $existingIds = collect($recipients)
                ->pluck('id')
                ->filter()
                ->toArray();

            // Soft delete penerima yang tidak ada di request
            LetterRecipient::where('letter_id', $letter->id)
                ->whereNotIn('id', $existingIds)
                ->each(fn (LetterRecipient $recipient) => $recipient->delete());

            $newRecipients = [];

            foreach ($recipients as $recipient) {
                if (! empty($recipient['id'])) {
                    // Update penerima yang sudah ada
                    LetterRecipient::where('id', $recipient['id'])
                        ->where('letter_id', $letter->id)
                        ->update([
                            'to_position_id' => $recipient['to_position_id'],
                            'instruction' => $recipient['instruction'] ?? null,
                        ]);
                } else {
                    // Tambah penerima baru
                    $newRecipient = LetterRecipient::create([
                        'letter_id' => $letter->id,
                        'from_user_id' => $user->id,
                        'from_position_id' => $currentPosition?->id,
                        'to_position_id' => $recipient['to_position_id'],
                        'instruction' => $recipient['instruction'] ?? null,
                        'received_at' => now(),
                    ]);

                    $newRecipients[] = $newRecipient;
                }
            }

            // Log aktivitas perubahan penerima
            $removedIds = LetterRecipient::where('letter_id', $letter->id)
                ->onlyTrashed()
                ->whereDate('deleted_at', today())
                ->with('toPosition:id,name')
                ->get();

            $changes = [];
            if ($removedIds->isNotEmpty()) {
                $changes['removed'] = $removedIds->map(fn ($r) => $r->toPosition->name ?? $r->to_position_id)->values()->toArray();
            }
            if (! empty($newRecipients)) {
                $changes['added'] = collect($newRecipients)->map(fn ($r) => Position::find($r->to_position_id)?->name ?? $r->to_position_id)->values()->toArray();
            }

            if (! empty($changes)) {
                LetterActivityService::log(
                    $letter->id,
                    'update_recipients',
                    'Memperbarui data penerima surat',
                    $changes
                );
            }
        });
    }
}

<?php

namespace App\Actions\Letter;

use App\Models\Letter;
use App\Services\LetterActivityService;

class UpdateSentLetterAction
{
    /**
     * Update sent letter attributes and log change diff.
     *
     * @param  array<string, mixed>  $validated
     */
    public function handle(Letter $letter, array $validated): Letter
    {
        $oldData = $letter->only(array_keys($validated));
        $letter->update($validated);

        LetterActivityService::logDiff(
            $letter->id,
            'update_letter',
            'Memperbarui data surat',
            $oldData,
            $validated
        );

        return $letter;
    }
}

<?php

namespace App\Actions\Letter;

use App\Models\Disposition;
use App\Models\Letter;
use App\Models\LetterDoc;
use App\Models\LetterRecipient;
use App\Models\Memo;
use Illuminate\Support\Facades\DB;

class DeleteSentLetterAction
{
    /**
     * Soft delete a sent letter together with every relation defined on the model.
     */
    public function handle(Letter $letter): void
    {
        DB::transaction(function () use ($letter): void {
            // Query per model, bukan lewat relasi: relasi `docs()` hanya
            // mengembalikan lampiran aktif, sedangkan seluruh relasi surat
            // ikut di-soft delete.
            Disposition::where('letter_id', $letter->id)->delete();
            Memo::where('letter_id', $letter->id)->delete();
            LetterRecipient::where('letter_id', $letter->id)->delete();
            LetterDoc::where('letter_id', $letter->id)->delete();

            $letter->delete();
        });
    }
}

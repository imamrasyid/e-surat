<?php

namespace App\Events;

use App\Models\Disposition;
use App\Models\LetterRecipient;
use App\Models\Memo;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Satu kontrak untuk setiap row yang menambah inbox seorang pemegang jabatan.
 *
 * Event ini ditunda sampai transaksi pembuatnya commit, sehingga rollback tidak
 * pernah menghasilkan pemberitahuan.
 */
class InboxItemCreated implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public LetterRecipient|Disposition|Memo $item
    ) {}
}

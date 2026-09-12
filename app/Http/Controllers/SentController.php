<?php

namespace App\Http\Controllers;

use App\Models\Disposition;
use App\Models\Letter;
use App\Models\LetterDoc;
use App\Models\LetterRecipient;
use App\Models\Memo;
use App\Models\Position;
use App\Models\RefAgendaSurat;
use App\Models\User;
use App\Services\LetterActivityService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Illuminate\Support\Facades\Storage;
use Inertia\Response;

class SentController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @return \Inertia\Response
     */
    public function index(): Response
    {
        $sent = Letter::where('type', 'in')
            ->select('letter_id', 'agenda', 'sender', 'subject', 'number', 'status', 'created_at')
            ->where(function (Builder $query) {
                $search = FacadesRequest::input('search');
                if ($search) {
                    $query->whereLike('agenda', '%' . $search . '%')
                        ->orWhereLike('sender', '%' . $search . '%')
                        ->orWhereLike('subject', '%' . $search . '%')
                        ->orWhereLike('number', '%' . $search . '%');
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return inertia('app/sent/Index', [
            'breadcrumbItems' => [
                [
                    'title' => 'Terkirim',
                    'href' => '/sent',
                ],
            ],
            'sent' => fn() => $sent,
        ]);
    }

    /**
     * Display the specified resource.
     * 
     * @param  string  $letter_id
     * @return \Inertia\Response
     */
    public function show(string $letter_id): Response
    {
        $letter = Letter::where('letter_id', $letter_id)
            ->where('type', 'in')
            ->with([
                'senderUser:id,name',
                'recipients:id,letter_id,from_position_id,to_position_id,instruction,received_at,read_at',
                'docs:id,letter_id,name,url'
            ])
            ->firstOrFail();

        return inertia('app/sent/Show', [
            'breadcrumbItems' => [
                [
                    'title' => 'Terkirim',
                    'href' => '/sent',
                ],
                [
                    'title' => 'Detail Surat',
                    'href' => '',
                ],
            ],
            'letter' => fn() => $letter,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     * 
     * @param  string  $letter_id
     * @return \Inertia\Response
     */
    public function editLetter(string $letter_id): Response
    {
        $letter = Letter::where('letter_id', $letter_id)
            ->where('status', 'open')
            ->firstOrFail();

        return inertia('app/sent/EditLetter', [
            'breadcrumbItems' => [
                [
                    'title' => 'Terkirim',
                    'href' => '/sent',
                ],
                [
                    'title' => 'Perbarui Surat',
                    'href' => '',
                ],
            ],
            'letter' => fn() => $letter,
        ]);
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $letter_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateLetter(Request $request, string $letter_id): RedirectResponse
    {
        $letter = Letter::where('letter_id', $letter_id)
            ->where('status', 'open')
            ->firstOrFail();

        $validated = $request->validate([
            'sender' => 'required|string|max:255',
            'number' => 'required|string|max:255',
            'date' => 'required|date',
            'receipt_date' => 'required|date',
            'category' => 'required|in:1,2',
            'subject' => 'required|string|max:1000',
        ]);

        $oldData = $letter->only(array_keys($validated));
        $letter->update($validated);

        LetterActivityService::logDiff(
            $letter->id,
            'update_letter',
            'Memperbarui data surat',
            $oldData,
            $validated
        );

        return redirect()->route('sent.show', $letter_id);
    }

    /**
     * Show the form for editing the recipients of the specified resource.
     * 
     * @param  string  $letter_id
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function editRecipients(string $letter_id): Response|RedirectResponse
    {
        $letter = Letter::where('letter_id', $letter_id)
            ->where('status', 'open')
            ->with(['recipients'])
            ->firstOrFail();

        // Cek apakah ada penerima yang sudah membuat disposisi atau memo
        $recipientIds = $letter->recipients->pluck('id');
        $hasActivity = Disposition::whereIn('letter_recipient_id', $recipientIds)->exists()
            || Memo::whereIn('letter_recipient_id', $recipientIds)->exists();

        if ($hasActivity) {
            return redirect()->route('sent.show', $letter_id)
                ->withErrors(['confirm' => 'Data penerima tidak dapat diperbarui karena sudah ada penerima yang membuat disposisi atau memo.']);
        }

        $positions = Position::query()
            ->select('id', 'name')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return inertia('app/sent/EditRecipients', [
            'breadcrumbItems' => [
                [
                    'title' => 'Terkirim',
                    'href' => '/sent',
                ],
                [
                    'title' => 'Perbarui Penerima',
                    'href' => '',
                ],
            ],
            'letter' => fn() => $letter,
            'positions' => fn() => $positions,
        ]);
    }

    /**
     * Update the recipients of the specified resource in storage.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $letter_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateRecipients(Request $request, string $letter_id): RedirectResponse
    {
        $letter = Letter::where('letter_id', $letter_id)
            ->where('status', 'open')
            ->firstOrFail();

        // Cek apakah ada penerima yang sudah membuat disposisi atau memo
        $recipientIds = LetterRecipient::where('letter_id', $letter->id)->pluck('id');
        $hasActivity = Disposition::whereIn('letter_recipient_id', $recipientIds)->exists()
            || Memo::whereIn('letter_recipient_id', $recipientIds)->exists();

        if ($hasActivity) {
            return redirect()->route('sent.show', $letter_id)
                ->withErrors(['confirm' => 'Data penerima tidak dapat diperbarui karena sudah ada penerima yang membuat disposisi atau memo.']);
        }

        $request->validate([
            'recipients' => 'required|array|min:1',
            'recipients.*.id' => 'nullable|integer|exists:letter_recipients,id',
            'recipients.*.to_position_id' => 'required|integer|exists:positions,id',
            'recipients.*.instruction' => 'nullable|string|max:500',
        ]);

        $user = User::find(Auth::user()->id);
        $currentPosition = $user->currentPosition()->first();

        DB::beginTransaction();

        try {
            $existingIds = collect($request->recipients)
                ->pluck('id')
                ->filter()
                ->toArray();

            // Soft delete penerima yang tidak ada di request
            LetterRecipient::where('letter_id', $letter->id)
                ->whereNotIn('id', $existingIds)
                ->each(fn($recipient) => $recipient->delete());

            $newRecipients = [];

            foreach ($request->recipients as $recipient) {
                if ($recipient['id']) {
                    // Update penerima yang sudah ada
                    LetterRecipient::where('id', $recipient['id'])
                        ->where('letter_id', $letter->id)
                        ->update([
                            'to_position_id' => $recipient['to_position_id'],
                            'instruction' => $recipient['instruction'],
                        ]);
                } else {
                    // Tambah penerima baru
                    $newRecipient = LetterRecipient::create([
                        'letter_id' => $letter->id,
                        'from_user_id' => $user->id,
                        'from_position_id' => $currentPosition->id,
                        'to_position_id' => $recipient['to_position_id'],
                        'instruction' => $recipient['instruction'],
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
                $changes['removed'] = $removedIds->map(fn($r) => $r->toPosition->name ?? $r->to_position_id)->values()->toArray();
            }
            if (!empty($newRecipients)) {
                $changes['added'] = collect($newRecipients)->map(fn($r) => Position::find($r->to_position_id)?->name ?? $r->to_position_id)->values()->toArray();
            }

            if (!empty($changes)) {
                LetterActivityService::log(
                    $letter->id,
                    'update_recipients',
                    'Memperbarui data penerima surat',
                    $changes
                );
            }

            DB::commit();

            // Kirim notifikasi Telegram untuk penerima baru
            // foreach ($newRecipients as $newRecipient) {
            //     $positionUser = Position::find($newRecipient->to_position_id)
            //         ->latestActiveUser()
            //         ->first();

            //     if ($positionUser && $positionUser->telegram_chat_id) {
            //         TelegramService::sendMessage(
            //             $positionUser->telegram_chat_id,
            //             "Halo! 📨\n\nAnda mendapatkan <b>Surat Masuk</b> baru, dengan nomor agenda sebagai berikut:\n<b>{$letter->agenda}</b>\n\nTerima kasih."
            //         );
            //     }
            // }

            return redirect()->route('sent.show', $letter_id);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error updating letter recipients: ' . $th->getMessage());
            return redirect()->back()->withErrors(['confirm' => 'Terjadi kesalahan saat memperbarui penerima surat.']);
        }
    }

    /**
     * Show the form for editing the documents of a sent letter.
     *
     * @param string $letter_id
     * @return \Inertia\Response
     */
    public function editDocs(string $letter_id): Response
    {
        $letter = Letter::where('letter_id', $letter_id)
            ->where('status', 'open')
            ->with(['docs:id,letter_id,name,url'])
            ->firstOrFail();

        return inertia('app/sent/EditDocs', [
            'breadcrumbItems' => [
                [
                    'title' => 'Terkirim',
                    'href' => '/sent',
                ],
                [
                    'title' => 'Perbarui Lampiran',
                    'href' => '',
                ],
            ],
            'letter' => fn() => $letter,
        ]);
    }

    /**
     * Update the documents of a sent letter.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $letter_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateDocs(Request $request, string $letter_id): RedirectResponse
    {
        $letter = Letter::where('letter_id', $letter_id)
            ->where('status', 'open')
            ->firstOrFail();

        $request->validate([
            'hapus' => 'nullable|array',
            'hapus.*' => 'integer|exists:letter_docs,id',
            'lampiran' => 'nullable|array',
            'lampiran.*' => 'file|mimes:pdf',
        ]);

        DB::beginTransaction();

        try {
            // Hapus lampiran (set is_active = false dan hapus file dari storage)
            $docsToDelete = collect();
            if ($request->hapus && count($request->hapus) > 0) {
                $docsToDelete = LetterDoc::where('letter_id', $letter->id)
                    ->whereIn('id', $request->hapus)
                    ->where('is_active', true)
                    ->get();

                foreach ($docsToDelete as $doc) {
                    // Hapus file dari storage
                    $storagePath = str_replace('/storage/', '', $doc->url);
                    if (Storage::disk('public')->exists($storagePath)) {
                        Storage::disk('public')->delete($storagePath);
                    }

                    // Set is_active = false
                    $doc->update(['is_active' => false]);
                }
            }

            // Tambah lampiran baru
            if ($request->hasFile('lampiran')) {
                $agenda = RefAgendaSurat::where('nomor_agenda', $letter->agenda)->firstOrFail();
                $klasifikasi = $agenda->klasifikasi;

                $receiptDate = date('Y-m-d', strtotime($letter->receipt_date));

                foreach ($request->file('lampiran') as $lampiran) {
                    $path = 'attachment/' . $receiptDate;
                    $filename = $klasifikasi->kode . $agenda->nomor_urut . '_' . $lampiran->getClientOriginalName();
                    Storage::disk('public')->putFileAs($path, $lampiran, $filename);

                    $storeUrl = "/storage/{$path}/{$filename}";
                    LetterDoc::create([
                        'agenda_id' => $agenda->id,
                        'letter_id' => $letter->id,
                        'name'      => $filename,
                        'url'       => $storeUrl,
                    ]);
                }
            }

            // Log aktivitas perubahan lampiran
            $changes = [];
            if ($docsToDelete->isNotEmpty()) {
                $changes['removed'] = $docsToDelete->pluck('name')->toArray();
            }
            if ($request->hasFile('lampiran')) {
                $changes['added'] = collect($request->file('lampiran'))
                    ->map(fn($f) => $f->getClientOriginalName())
                    ->toArray();
            }

            if (!empty($changes)) {
                LetterActivityService::log(
                    $letter->id,
                    'update_docs',
                    'Memperbarui lampiran surat',
                    $changes
                );
            }

            DB::commit();

            return redirect()->route('sent.show', $letter_id);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error updating letter documents: ' . $th->getMessage());
            return redirect()->back()->withErrors(['confirm' => 'Terjadi kesalahan saat memperbarui lampiran surat.']);
        }
    }
}

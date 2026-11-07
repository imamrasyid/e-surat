<?php

namespace App\Http\Controllers;

use App\Actions\Letter\DeleteSentLetterAction;
use App\Actions\Letter\UpdateSentDocsAction;
use App\Actions\Letter\UpdateSentLetterAction;
use App\Actions\Letter\UpdateSentRecipientsAction;
use App\Http\Requests\UpdateSentLetterRequest;
use App\Http\Requests\UpdateSentRecipientsRequest;
use App\Models\Disposition;
use App\Models\Letter;
use App\Models\LetterRecipient;
use App\Models\Memo;
use App\Models\Position;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class SentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $sent = Letter::where('type', 'in')
            ->select('letter_id', 'category', 'agenda', 'sender', 'subject', 'number', 'status', 'created_at')
            ->when($request->input('search'), function (Builder $query, string $search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->whereLike('agenda', '%'.$search.'%')
                        ->orWhereLike('sender', '%'.$search.'%')
                        ->orWhereLike('subject', '%'.$search.'%')
                        ->orWhereLike('number', '%'.$search.'%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('app/sent/Index', [
            'breadcrumbItems' => [
                [
                    'title' => 'Terkirim',
                    'route' => 'sent.index',
                ],
            ],
            'sent' => fn () => $sent,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $letter_id): Response
    {
        $letter = Letter::where('letter_id', $letter_id)
            ->where('type', 'in')
            ->with([
                'senderUser:id,name',
                'recipients:id,letter_id,from_position_id,to_position_id,instruction,received_at,read_at',
                'docs:id,letter_id,name',
            ])
            ->firstOrFail();

        return Inertia::render('app/sent/Show', [
            'breadcrumbItems' => [
                [
                    'title' => 'Terkirim',
                    'route' => 'sent.index',
                ],
                [
                    'title' => 'Detail Surat',
                ],
            ],
            'letter' => fn () => $letter,
        ]);
    }

    /**
     * Soft delete the specified letter and its related records.
     */
    public function destroy(string $letter_id, DeleteSentLetterAction $action): RedirectResponse
    {
        $letter = Letter::where('letter_id', $letter_id)
            ->where('type', 'in')
            ->firstOrFail();

        try {
            $action->handle($letter);

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Surat berhasil dihapus.',
            ]);

            return redirect()->route('sent.index');
        } catch (\Throwable $th) {
            Log::error('Error deleting letter: '.$th->getMessage());

            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Gagal menghapus surat: '.$th->getMessage(),
            ]);

            return redirect()->back();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editLetter(string $letter_id): Response
    {
        $letter = Letter::where('letter_id', $letter_id)
            ->where('status', 'open')
            ->firstOrFail();

        return Inertia::render('app/sent/EditLetter', [
            'breadcrumbItems' => [
                [
                    'title' => 'Terkirim',
                    'route' => 'sent.index',
                ],
                [
                    'title' => 'Perbarui Surat',
                ],
            ],
            'letter' => fn () => $letter,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateLetter(
        UpdateSentLetterRequest $request,
        string $letter_id,
        UpdateSentLetterAction $action
    ): RedirectResponse {
        try {
            $letter = Letter::where('letter_id', $letter_id)
                ->where('status', 'open')
                ->firstOrFail();

            $action->handle($letter, $request->validated());

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Data surat berhasil diperbarui.',
            ]);

            return redirect()->route('sent.index');
        } catch (\Throwable $th) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Gagal memperbarui data surat: '.$th->getMessage(),
            ]);

            return redirect()->back();
        }
    }

    /**
     * Show the form for editing the recipients of the specified resource.
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
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Data penerima tidak dapat diperbarui karena sudah ada penerima yang membuat disposisi atau memo.',
            ]);

            return redirect()->route('sent.show', $letter_id);
        }

        $positions = Position::query()
            ->select('id', 'name')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('app/sent/EditRecipients', [
            'breadcrumbItems' => [
                [
                    'title' => 'Terkirim',
                    'route' => 'sent.index',
                ],
                [
                    'title' => 'Perbarui Penerima',
                ],
            ],
            'letter' => fn () => $letter,
            'positions' => fn () => $positions,
        ]);
    }

    /**
     * Update the recipients of the specified resource in storage.
     */
    public function updateRecipients(
        UpdateSentRecipientsRequest $request,
        string $letter_id,
        UpdateSentRecipientsAction $action
    ): RedirectResponse {
        $letter = Letter::where('letter_id', $letter_id)
            ->where('status', 'open')
            ->firstOrFail();

        // Cek apakah ada penerima yang sudah membuat disposisi atau memo
        $recipientIds = LetterRecipient::where('letter_id', $letter->id)->pluck('id');
        $hasActivity = Disposition::whereIn('letter_recipient_id', $recipientIds)->exists()
            || Memo::whereIn('letter_recipient_id', $recipientIds)->exists();

        if ($hasActivity) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Data penerima tidak dapat diperbarui karena sudah ada penerima yang membuat disposisi atau memo.',
            ]);

            return redirect()->route('sent.show', $letter_id);
        }

        $validated = $request->validated();
        $user = $request->user();
        $currentPosition = $user?->currentPosition()?->first();

        try {
            $action->handle($letter, $user, $currentPosition, $validated['recipients']);

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Data penerima surat berhasil diperbarui.',
            ]);

            return redirect()->route('sent.index');
        } catch (\Throwable $th) {
            Log::error('Error updating letter recipients: '.$th->getMessage());

            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui penerima surat.',
            ]);

            return redirect()->back();
        }
    }

    /**
     * Show the form for editing the documents of a sent letter.
     */
    public function editDocs(string $letter_id): Response
    {
        $letter = Letter::where('letter_id', $letter_id)
            ->where('status', 'open')
            ->with(['docs:id,letter_id,name'])
            ->firstOrFail();

        return Inertia::render('app/sent/EditDocs', [
            'breadcrumbItems' => [
                [
                    'title' => 'Terkirim',
                    'route' => 'sent.index',
                ],
                [
                    'title' => 'Perbarui Lampiran',
                ],
            ],
            'letter' => fn () => $letter,
        ]);
    }

    /**
     * Update the documents of a sent letter.
     */
    public function updateDocs(
        Request $request,
        string $letter_id,
        UpdateSentDocsAction $action
    ): RedirectResponse {
        $letter = Letter::where('letter_id', $letter_id)
            ->where('status', 'open')
            ->firstOrFail();

        $request->validate([
            'hapus' => ['nullable', 'array'],
            'hapus.*' => ['integer', 'exists:letter_docs,id'],
            'lampiran' => ['nullable', 'array'],
            'lampiran.*' => ['file', 'mimes:pdf'],
        ]);

        try {
            $action->handle(
                $letter,
                $request->input('hapus', []),
                $request->file('lampiran', [])
            );

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Lampiran surat berhasil diperbarui.',
            ]);

            return redirect()->route('sent.index');
        } catch (\Throwable $th) {
            Log::error('Error updating letter documents: '.$th->getMessage());

            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui lampiran surat: '.$th->getMessage(),
            ]);

            return redirect()->back();
        }
    }
}

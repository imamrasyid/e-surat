<?php

namespace App\Http\Controllers\Inbox;

use App\Http\Controllers\Controller;
use App\Models\Letter;
use App\Models\LetterRecipient;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Inertia\Response;

class PrimaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $user = User::find(Auth::user()->id);
        $currentPosition = $user->currentPosition()->first();

        $primary = Letter::query()
            ->select('letters.letter_id', 'agenda', 'sender', 'subject', 'number', 'letter_recipients.id as letter_recipient_id', 'received_at', 'read_at')
            ->leftJoin('letter_recipients', 'letters.id', '=', 'letter_recipients.letter_id')
            ->where('type', 'in')
            ->where('category', '1')
            ->where('letter_recipients.to_position_id', $currentPosition->id)
            ->where(function(Builder $query) {
                $search = FacadesRequest::input('search');
                if ($search) {
                    $query->whereLike('agenda','%'.$search.'%')
                        ->orWhereLike('sender','%'.$search.'%')
                        ->orWhereLike('subject','%'.$search.'%')
                        ->orWhereLike('number','%'.$search.'%');
                }
            })
            ->where(function(Builder $query) {
                $filter = FacadesRequest::input('filter');
                if ($filter == 'read') {
                    $query->whereNotNull('read_at');
                }
                if ($filter == 'unread') {
                    $query->whereNull('read_at');
                }
            })
            ->orderBy('received_at', 'desc')
            ->paginate(15);

        return inertia('app/primary/Index', [
            'breadcrumbItems' => [
                [
                    'title' => 'Primary',
                    'href' => '/inbox/primary',
                ],
            ],
            'primary' => fn() => $primary,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $letter_recipient_id, string $letter_id): Response
    {
        $letter = Letter::where('letter_id', $letter_id)
            ->where('type', 'in')
            ->where('category', '1')
            ->with([
                'senderUser:id,name',
                'recipients:id,letter_id,from_position_id,to_position_id,instruction,received_at,read_at',
                'docs:id,letter_id,name,url'])
            ->whereHas('recipients', function($query) use ($letter_recipient_id) {
                $query->where('id', $letter_recipient_id);
            })
            ->firstOrFail();
        
        return inertia('app/primary/Show', [
            'breadcrumbItems' => [
                [
                    'title' => 'Primary',
                    'href' => '/inbox/primary',
                ],
                [
                    'title' => 'Detail Surat',
                    'href' => '',
                ],
            ],
            'letter' => fn() => $letter,
            'recipient_id' => fn() => $letter_recipient_id,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(string $letter_recipient_id, string $letter_id): RedirectResponse
    {
        $letterRecipient = LetterRecipient::find($letter_recipient_id);
        if (!$letterRecipient) {
            return redirect()->back()->withErrors(['field' => 'Field not found.']);
        }
        
        DB::beginTransaction();

        try {
            $letterRecipient->update(['read_at' => now()]);

            DB::commit();

            return redirect()->route('inbox.primary.show', (['letter_recipient_id' => $letter_recipient_id, 'letter_id' => $letter_id]));

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->withErrors(['confirm' => $th->getMessage()]);
        }
    }
}

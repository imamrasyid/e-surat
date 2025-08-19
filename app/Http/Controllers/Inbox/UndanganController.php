<?php

namespace App\Http\Controllers\Inbox;

use App\Http\Controllers\Controller;
use App\Models\LetterRecipient;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Inertia\Response;

class UndanganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $user = User::find(Auth::user()->id);
        $currentPosition = $user?->currentPosition()?->first();

        // Kalau user tidak punya currentPosition
        if (!$currentPosition) {
            return inertia('app/undangan/Index', [
                'breadcrumbItems' => [
                    [
                        'title' => 'Undangan',
                        'href' => '/inbox/undangan',
                    ],
                ],
                'undangan' => fn() => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15)
            ]);
        }

        $undangan = LetterRecipient::query()
            ->select('id', 'letter_id', 'received_at', 'read_at')
            ->where('to_position_id', $currentPosition->id)
            ->with(['letter' => function($query) {
                $query->select('id', 'letter_id', 'agenda', 'sender', 'number', 'subject');
            }])
            ->whereHas('letter', function(Builder $query) {
                $query->where('type', 'in')
                    ->where('category', '2');
            })
            ->when(FacadesRequest::input('search'), function(Builder $query, $search) {
                $query->whereHas('letter', function($subQuery) use ($search) {
                    $subQuery->whereLike('agenda', '%'.$search.'%')
                        ->orWhereLike('sender', '%'.$search.'%')
                        ->orWhereLike('subject', '%'.$search.'%')
                        ->orWhereLike('number', '%'.$search.'%');
                });
            })
            ->when(FacadesRequest::input('filter'), function (Builder $query, $filter) {
                if ($filter == 'read') {
                    $query->whereNotNull('read_at');
                } elseif ($filter == 'unread') {
                    $query->whereNull('read_at');
                }
            })
            ->orderBy('received_at', 'desc')
            ->paginate(15);
        
        
        return inertia('app/undangan/Index', [
            'breadcrumbItems' => [
                [
                    'title' => 'Undangan',
                    'href' => '/inbox/undangan',
                ],
            ],
            'undangan' => fn() => $undangan,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(LetterRecipient $recipient, string $letter_id): Response | RedirectResponse
    {
        $user = User::find(Auth::user()->id);
        $currentPosition = $user->currentPosition()->first();

        if ($recipient->to_position_id !== $currentPosition->id) {
            return redirect()->back()->withErrors(['confirm' => 'Anda tidak memiliki akses ke surat ini.']);
        }

        if ($recipient->letter()->where('letter_id', $letter_id)->doesntExist()) {
            return redirect()->back()->withErrors(['confirm' => 'Surat tidak ditemukan atau tidak sesuai dengan penerima surat.']);
        }

        if (FacadesRequest::isMethod('patch')) {
            DB::beginTransaction();

            try {
                $recipient->update(['read_at' => now()]);

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                return redirect()->back()->withErrors(['confirm' => $th->getMessage()]);
            }
        }

        $letter = $recipient->letter()->with([
            'senderUser:id,name',
            'recipients:id,letter_id,from_position_id,to_position_id,instruction,received_at,read_at',
            'docs:id,letter_id,name,url'
        ])->firstOrFail();
        
        return inertia('app/undangan/Show', [
            'breadcrumbItems' => [
                [
                    'title' => 'Undangan',
                    'href' => '/inbox/undangan',
                ],
                [
                    'title' => 'Detail Surat',
                    'href' => '',
                ],
            ],
            'letter' => fn() => $letter,
            'recipient' => fn() => $recipient->id,
        ]);
    }
}

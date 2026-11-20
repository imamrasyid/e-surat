<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ArsipController extends Controller
{
    /**
     * Display a listing of closed incoming letters.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $search = is_string($search) ? $search : '';

        $letters = Letter::where('type', 'in')
            ->where('status', 'close')
            ->select('id', 'letter_id', 'category', 'agenda', 'sender', 'number', 'subject', 'receipt_date', 'status')
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->whereLike('agenda', '%'.$search.'%')
                        ->orWhereLike('sender', '%'.$search.'%')
                        ->orWhereLike('subject', '%'.$search.'%')
                        ->orWhereLike('number', '%'.$search.'%');
                });
            })
            ->orderBy('receipt_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('archive/Index', [
            'breadcrumbItems' => [
                [
                    'title' => 'Arsip Surat',
                    'route' => 'arsip.index',
                ],
            ],
            'letters' => fn () => $letters,
        ]);
    }

    /**
     * Display a closed incoming letter as read-only archive detail.
     */
    public function show(string $letter_id): Response
    {
        $letter = Letter::where('letter_id', $letter_id)
            ->where('type', 'in')
            ->where('status', 'close')
            ->with([
                'senderUser:id,name',
                'recipients:id,letter_id,from_position_id,to_position_id,instruction,received_at,read_at',
                'docs:id,letter_id,name',
                'activities.user:id,name',
            ])
            ->firstOrFail();

        return Inertia::render('archive/Show', [
            'breadcrumbItems' => [
                [
                    'title' => 'Arsip Surat',
                    'route' => 'arsip.index',
                ],
                [
                    'title' => 'Detail Arsip',
                ],
            ],
            'letter' => fn () => $letter,
        ]);
    }
}

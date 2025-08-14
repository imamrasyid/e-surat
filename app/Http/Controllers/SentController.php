<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Inertia\Response;

class SentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $sent = Letter::where('type', 'in')
            ->select('letter_id', 'agenda', 'sender', 'subject', 'number', 'status', 'created_at')
            ->where(function(Builder $query) {
                $search = FacadesRequest::input('search');
                if ($search) {
                    $query->whereLike('agenda','%'.$search.'%')
                        ->orWhereLike('sender','%'.$search.'%')
                        ->orWhereLike('subject','%'.$search.'%')
                        ->orWhereLike('number','%'.$search.'%');
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
     */
    public function show(string $letter_id): Response
    {
        $letter = Letter::where('letter_id', $letter_id)
            ->where('type', 'in')
            ->with([
                'senderUser:id,name',
                'recipients:id,letter_id,from_position_id,to_position_id,instruction,received_at,read_at',
                'docs:id,letter_id,name,url'])
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
}

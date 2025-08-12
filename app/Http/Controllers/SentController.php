<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            ->select('letter_id', 'agenda', 'sender', 'subject', 'number', 'created_at')
            ->where(function(Builder $query) {
                $search = FacadesRequest::input('search');
                if ($search) {
                    $query->whereLike('agenda','%'.$search.'%')
                        ->orWhereLike('sender','%'.$search.'%')
                        ->orWhereLike('subject','%'.$search.'%')
                        ->orWhereLike('number','%'.$search.'%');
                }
            })
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
                'docs:id,letter_id,url'])
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
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as FacadesRequest;

class SentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $sent = Letter::where('type', 'in')
            ->where('created_by', $user->id)
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
    public function show(string $id)
    {
        //
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

<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class ArsipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('archive/Index', [
            'breadcrumbItems' => [
                [
                    'title' => 'Arsip Surat',
                    'href' => '/archive',
                ],
            ],
        ]);
    }
}

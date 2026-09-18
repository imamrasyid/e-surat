<?php

namespace App\Http\Controllers;

use Inertia\Response;

class ArsipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return inertia('archive/Index', [
            'breadcrumbItems' => [
                [
                    'title' => 'Arsip Surat',
                    'href' => '/archive',
                ],
            ],
        ]);
    }
}

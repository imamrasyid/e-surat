<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\RefKdKlasifikasi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Response;
use Illuminate\Support\Facades\Request as FacadesRequest;

class KodeKlasifikasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $data = RefKdKlasifikasi::query()
            ->select('id', 'kode', 'nama', 'is_active')
            ->where(function(Builder $query) {
                $search = FacadesRequest::input('search');
                if ($search) {
                    $query->whereLike('kode','%'.$search.'%')
                        ->orWhereLike('nama','%'.$search.'%');
                }
            })
            ->paginate(10);

        return inertia('master/klasifikasi-arsip/Index', [
            'breadcrumbItems' => [
                [
                    'title' => 'Klasifikasi Arsip',
                    'href' => '/master-data/klasifikasi-arsip',
                ],
            ],
            'data' => fn() => $data,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return inertia('master/klasifikasi-arsip/Create', [
            'breadcrumbItems' => [
                [
                    'title' => 'Klasifikasi Arsip',
                    'href' => '/master-data/klasifikasi-arsip',
                ],
                [
                    'title' => 'Tambah Baru',
                    'href' => '/master-data/klasifikasi-arsip/create',
                ],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'kode' => 'required|numeric|min:3|unique:ref_kode_klasifikasi_arsip,kode',
            'nama' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $data = $request->all();

            RefKdKlasifikasi::create($data);

            DB::commit();

            return redirect()->route('ref.klasifikasi.index');

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->withErrors(['confirm' => $th->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RefKdKlasifikasi $kode): Response
    {
        return inertia('master/klasifikasi-arsip/Edit', [
            'breadcrumbItems' => [
                [
                    'title' => 'Klasifikasi Arsip',
                    'href' => '/master-data/klasifikasi-arsip',
                ],
                [
                    'title' => 'Perbarui',
                    'href' => '/master-data/klasifikasi-arsip/edit',
                ],
            ],
            'data' => fn() => $kode
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RefKdKlasifikasi $kode): RedirectResponse
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'is_active' => 'required|boolean'
        ]);

        DB::beginTransaction();

        try {
            $kode->update([
                'nama' => $request->nama,
                'is_active' => $request->is_active
            ]);

            DB::commit();

            return redirect()->route('ref.klasifikasi.index');

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->withErrors(['confirm' => $th->getMessage()]);
        }
    }
}

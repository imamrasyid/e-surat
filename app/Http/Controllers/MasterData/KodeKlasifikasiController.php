<?php

namespace App\Http\Controllers\MasterData;

use App\Actions\MasterData\KodeKlasifikasi\CreateKodeKlasifikasiAction;
use App\Actions\MasterData\KodeKlasifikasi\UpdateKodeKlasifikasiAction;
use App\Http\Controllers\Controller;
use App\Models\RefKdKlasifikasi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class KodeKlasifikasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $data = RefKdKlasifikasi::query()
            ->select('id', 'kode', 'nama', 'is_active')
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $search = $request->input('search');
                $query->where(function (Builder $q) use ($search) {
                    $q->whereLike('kode', '%'.$search.'%')
                        ->orWhereLike('nama', '%'.$search.'%');
                });
            })
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('master/klasifikasi-arsip/Index', [
            'breadcrumbItems' => [
                [
                    'title' => 'Master Data',
                    'route' => 'ref.master-data',
                ],
                [
                    'title' => 'Klasifikasi Arsip',
                    'route' => 'ref.klasifikasi.index',
                ],
            ],
            'data' => fn () => $data,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('master/klasifikasi-arsip/Create', [
            'breadcrumbItems' => [
                [
                    'title' => 'Master Data',
                    'route' => 'ref.master-data',
                ],
                [
                    'title' => 'Klasifikasi Arsip',
                    'route' => 'ref.klasifikasi.index',
                ],
                [
                    'title' => 'Tambah Baru',
                ],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, CreateKodeKlasifikasiAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'kode' => ['required', 'numeric', 'digits:3', 'unique:ref_kode_klasifikasi_arsip,kode'],
            'nama' => ['required', 'string', 'max:255'],
        ], [
            'kode.required' => 'Kode klasifikasi wajib diisi.',
            'kode.numeric' => 'Kode klasifikasi harus berupa angka.',
            'kode.digits' => 'Kode klasifikasi harus 3 digit.',
            'kode.unique' => 'Kode klasifikasi sudah digunakan.',
            'nama.required' => 'Nama klasifikasi wajib diisi.',
            'nama.string' => 'Nama klasifikasi harus berupa teks.',
            'nama.max' => 'Nama klasifikasi maksimal 255 karakter.',
        ]);

        try {
            $action->handle($validated);

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Data kode klasifikasi berhasil disimpan.',
            ]);

            return to_route('ref.klasifikasi.index');
        } catch (Throwable $th) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Gagal menyimpan data: '.$th->getMessage(),
            ]);

            return back();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RefKdKlasifikasi $kode): Response
    {
        return Inertia::render('master/klasifikasi-arsip/Edit', [
            'breadcrumbItems' => [
                [
                    'title' => 'Master Data',
                    'route' => 'ref.master-data',
                ],
                [
                    'title' => 'Klasifikasi Arsip',
                    'route' => 'ref.klasifikasi.index',
                ],
                [
                    'title' => 'Perbarui',
                ],
            ],
            'data' => fn () => $kode,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        Request $request,
        RefKdKlasifikasi $kode,
        UpdateKodeKlasifikasiAction $action
    ): RedirectResponse {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ], [
            'nama.required' => 'Nama klasifikasi wajib diisi.',
            'nama.string' => 'Nama klasifikasi harus berupa teks.',
            'nama.max' => 'Nama klasifikasi maksimal 255 karakter.',
            'is_active.required' => 'Status aktif wajib dipilih.',
            'is_active.boolean' => 'Status aktif tidak valid.',
        ]);

        try {
            $action->handle($kode, $validated);

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Data kode klasifikasi berhasil diperbarui.',
            ]);

            return to_route('ref.klasifikasi.index');
        } catch (Throwable $th) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Gagal memperbarui data: '.$th->getMessage(),
            ]);

            return back();
        }
    }
}

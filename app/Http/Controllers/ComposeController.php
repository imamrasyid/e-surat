<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\LetterDoc;
use App\Models\LetterRecipient;
use App\Models\Position;
use App\Models\RefAgendaSurat;
use App\Models\RefKdKlasifikasi;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ComposeController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $kdKlasifikasi = RefKdKlasifikasi::query()
            ->select('id', 'kode', 'nama', 'is_active')
            ->where('is_active', true)
            ->orderBy('nama')
            ->get();

        $positions = Position::query()
            ->select('id', 'name')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('app/Compose', [
            'kdKlasifikasi' => fn () => $kdKlasifikasi,
            'positions' => fn () => $positions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = User::find(Auth::user()->id);
        $currentPosition = $user->currentPosition()->first();
        $customUlid = $this->customUlid();

        $request->validate([
            'pengirim' => 'required|string|max:150',
            'nomor' => 'nullable|string|max:100',
            'tglSuratStr' => 'required|date_format:Y-m-d',
            'tglDiterimaStr' => 'required|date_format:Y-m-d',
            'perihal' => 'required|string|max:255',
            'kategori' => 'required|digits:1',
            'agenda' => 'required|exists:ref_kode_klasifikasi_arsip,kode',
            'agendaTahun' => 'required|digits:4',
            'penerima' => 'required|array|min:1',
            'penerima.*.id' => 'required|integer',
            'penerima.*.name' => 'required|string',
            'catatan' => 'nullable|string',
            'lampiran' => 'required|array|min:1',
            'lampiran.*' => 'required|file|mimes:pdf',
        ]);

        DB::beginTransaction();

        try {
            // MEMBUAT NOMOR AGENDA
            $klasifikasi = RefKdKlasifikasi::where('kode', $request->agenda)->firstOrFail();
            $nomorUrutTerakhir = RefAgendaSurat::where('klasifikasi_id', $klasifikasi->id)
                ->where('tahun', $request->agendaTahun)
                ->max('nomor_urut');

            $nomorUrutBaru = $nomorUrutTerakhir ? $nomorUrutTerakhir + 1 : 1;
            $nomorAgenda = "{$nomorUrutBaru}/{$klasifikasi->kode}/{$request->agendaTahun}";
            $agenda = RefAgendaSurat::create([
                'klasifikasi_id' => $klasifikasi->id,
                'nomor_urut' => $nomorUrutBaru,
                'tahun' => $request->agendaTahun,
                'nomor_agenda' => $nomorAgenda,
            ]);

            // INSERT SURAT
            $letter = Letter::create([
                'letter_id' => $customUlid,
                'sender' => $request->pengirim,
                'number' => $request->nomor,
                'date' => $request->tglSuratStr,
                'receipt_date' => $request->tglDiterimaStr,
                'subject' => $request->perihal,
                'category' => $request->kategori,
                'agenda' => $nomorAgenda,
                'created_by' => $user->id,
            ]);

            // INSERT PENERIMA
            foreach ($request->penerima as $penerima) {
                LetterRecipient::create([
                    'letter_id' => $letter->id,
                    'from_user_id' => $user->id,
                    'from_position_id' => $currentPosition->id,
                    'to_position_id' => $penerima['id'],
                    'instruction' => $request->catatan,
                    'received_at' => now(),
                ]);
            }

            // INSERT LAMPIRAN
            foreach ($request->lampiran as $lampiran) {
                $path = 'attachment/'.$request->tglDiterimaStr;
                $filename = $klasifikasi->kode.$nomorUrutBaru.'_'.$lampiran->getClientOriginalName();
                Storage::disk('public')->putFileAs($path, $lampiran, $filename);

                $storeUrl = "/storage/{$path}/{$filename}";
                LetterDoc::create([
                    'agenda_id' => $agenda->id,
                    'letter_id' => $letter->id,
                    'name' => $filename,
                    'url' => $storeUrl,
                ]);
            }

            DB::commit();

            return redirect()->route('app.compose.create');

        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->back()->withErrors(['confirm' => $th->getMessage()]);
        }
    }

    private function customUlid()
    {
        $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $base58Length = 26;

        // Ambil ULID asli
        $ulid = Str::ulid()->toBase32();

        // Ambil 10 karakter pertama sebagai timestamp ULID (Base32)
        $timestampBase32 = substr($ulid, 0, 10);

        // Decode Base32 -> integer
        $base32Alphabet = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
        $value = 0;
        foreach (str_split($timestampBase32) as $char) {
            $value = $value * 32 + strpos($base32Alphabet, $char);
        }

        // Encode integer -> Base58
        $timestampBase58 = '';
        $maxIndex = strlen($alphabet) - 1;
        while ($value > 0) {
            $timestampBase58 = $alphabet[$value % 58].$timestampBase58;
            $value = intdiv($value, 58);
        }

        // Pastikan panjang konsisten (misal 8 karakter), pad jika perlu
        $timestampBase58 = str_pad($timestampBase58, 8, $alphabet[0], STR_PAD_LEFT);

        // Tambah "salt" random kecil (2 karakter) agar timestamp lebih unik
        $salt = '';
        for ($i = 0; $i < 2; $i++) {
            $salt .= $alphabet[random_int(0, $maxIndex)];
        }

        // Sisa karakter random untuk mencapai total panjang
        $remainingLength = $base58Length - strlen($timestampBase58) - strlen($salt);
        $randomPart = '';
        for ($i = 0; $i < $remainingLength; $i++) {
            $randomPart .= $alphabet[random_int(0, $maxIndex)];
        }

        return $timestampBase58.$salt.$randomPart;
    }
}

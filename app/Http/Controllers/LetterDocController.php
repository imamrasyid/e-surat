<?php

namespace App\Http\Controllers;

use App\Models\LetterDoc;
use App\Services\LetterAccess;
use App\Services\LetterDocStorage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Penyajian lampiran surat privat.
 *
 * File tidak pernah disajikan dari URL publik: setiap permintaan memeriksa
 * akses user ke surat terkait lalu mengalirkan byte dari disk privat.
 * `preview` mengirim inline untuk dialog pratinjau, `download` tetap
 * memaksa unduhan dengan nama asli dari database.
 */
class LetterDocController extends Controller
{
    public function download(
        Request $request,
        LetterDoc $doc,
        LetterAccess $access,
        LetterDocStorage $storage
    ): StreamedResponse {
        return $this->stream($request, $doc, $access, $storage, false);
    }

    public function preview(
        Request $request,
        LetterDoc $doc,
        LetterAccess $access,
        LetterDocStorage $storage
    ): StreamedResponse {
        return $this->stream($request, $doc, $access, $storage, true);
    }

    private function stream(
        Request $request,
        LetterDoc $doc,
        LetterAccess $access,
        LetterDocStorage $storage,
        bool $inline
    ): StreamedResponse {
        $user = $request->user();

        abort_if($user === null, 403);
        abort_unless($doc->is_active, 404);

        $letter = $doc->letter;

        abort_if($letter === null, 404);
        abort_unless($access->allows($user, $letter), 403);

        $path = (string) $doc->path;

        abort_unless($storage->exists($path), 404);

        return $inline
            ? $storage->preview($path, $doc->name)
            : $storage->download($path, $doc->name);
    }
}

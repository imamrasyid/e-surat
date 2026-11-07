<?php

namespace App\Http\Controllers;

use App\Models\LetterDoc;
use App\Services\LetterAccess;
use App\Services\LetterDocStorage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Unduhan lampiran surat privat.
 *
 * File tidak pernah disajikan dari URL publik: setiap permintaan memeriksa
 * akses user ke surat terkait lalu mengalirkan byte dari disk privat.
 */
class LetterDocDownloadController extends Controller
{
    public function __invoke(
        Request $request,
        LetterDoc $doc,
        LetterAccess $access,
        LetterDocStorage $storage
    ): StreamedResponse {
        $user = $request->user();

        abort_if($user === null, 403);
        abort_unless($doc->is_active, 404);

        $letter = $doc->letter;

        abort_if($letter === null, 404);
        abort_unless($access->allows($user, $letter), 403);
        abort_unless($storage->exists($doc->path), 404);

        return $storage->download($doc->path, $doc->name);
    }
}

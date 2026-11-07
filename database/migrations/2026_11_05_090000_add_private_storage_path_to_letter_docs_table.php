<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Lampiran surat pindah dari disk `public` + URL `/storage/...` ke path privat
 * pada disk default. Kolom `url` dipertahankan nullable sebagai data legacy
 * dan tidak lagi dibaca aplikasi; path adalah sumber kebenaran.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('letter_docs', function (Blueprint $table) {
            $table->string('path')->nullable()->after('name');
        });

        Schema::table('letter_docs', function (Blueprint $table) {
            $table->string('url')->nullable()->change();
        });

        DB::table('letter_docs')
            ->whereNull('path')
            ->where('url', 'like', '/storage/%')
            ->select(['id', 'url'])
            ->orderBy('id')
            ->chunkById(200, function (Collection $docs): void {
                foreach ($docs as $doc) {
                    $url = $doc->url;

                    if (! is_string($url) || ! str_starts_with($url, '/storage/')) {
                        continue;
                    }

                    DB::table('letter_docs')
                        ->where('id', $doc->id)
                        ->update(['path' => Str::after($url, '/storage/')]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('letter_docs', function (Blueprint $table) {
            $table->dropColumn('path');
        });

        Schema::table('letter_docs', function (Blueprint $table) {
            $table->string('url')->nullable(false)->change();
        });
    }
};

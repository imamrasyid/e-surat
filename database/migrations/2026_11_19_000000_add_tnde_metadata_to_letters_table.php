<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Metadata TNDE minimum untuk surat masuk. Data lama tidak memiliki nilai,
 * sehingga default `B` (Biasa/Terbuka) dan `other` dipakai agar surat lama
 * tetap tampil sebagai surat biasa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            $table->string('security_classification', 2)->default('B')->after('category');
            $table->string('received_via', 16)->default('other')->after('security_classification');
        });
    }

    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            $table->dropColumn(['security_classification', 'received_via']);
        });
    }
};

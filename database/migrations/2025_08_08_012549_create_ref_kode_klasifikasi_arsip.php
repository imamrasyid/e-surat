<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ref_kode_klasifikasi_arsip', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 3)->unique()->comment('Kode klasifikasi arsip');
            $table->string('nama')->comment('Nama klasifikasi arsip');
            $table->boolean('is_active')->default(true)->comment('Status aktif klasifikasi arsip');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_kode_klasifikasi_arsip');
    }
};

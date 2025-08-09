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
        Schema::create('ref_agenda_surat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('klasifikasi_id');
            $table->integer('nomor_urut');
            $table->year('tahun');
            $table->string('nomor_agenda'); // Format: 301/400/2024
            $table->timestamps();

            $table->foreign('klasifikasi_id')->references('id')->on('ref_kode_klasifikasi_arsip');
            $table->unique(['klasifikasi_id', 'nomor_urut', 'tahun']); // Hindari duplikat
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_agenda_surat');
    }
};

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
        Schema::table('letter_docs', function (Blueprint $table) {
            $table->unsignedBigInteger('agenda_id')->after('id');

            $table->foreign('agenda_id')->references('id')->on('ref_agenda_surat')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letter_docs', function (Blueprint $table) {
            $table->dropColumn('agenda_id');
        });
    }
};

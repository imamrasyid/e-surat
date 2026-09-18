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
        Schema::create('letters', function (Blueprint $table) {
            $table->id();
            $table->ulid('letter_id')->unique();
            $table->string('sender', 150);
            $table->string('number', 100)->nullable();
            $table->date('date');
            $table->date('receipt_date');
            $table->string('subject');
            $table->char('category', 1);
            $table->string('agenda', 50);
            $table->enum('type', ['in', 'out'])->default('in');
            $table->enum('status', ['open', 'close'])->default('open');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letters');
    }
};

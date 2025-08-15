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
        Schema::create('dispositions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('letter_id')->constrained('letters')->onDelete('cascade');
            $table->foreignId('letter_recipient_id')->constrained('letter_recipients')->onDelete('cascade');
            $table->boolean('is_cc')->default(false);
            $table->foreignId('from_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('from_position_id')->constrained('positions')->onDelete('cascade');
            $table->foreignId('to_position_id')->constrained('positions')->onDelete('cascade');
            $table->text('instruction')->nullable();
            $table->text('followup')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispositions');
    }
};

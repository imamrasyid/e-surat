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
        Schema::create('memos', function (Blueprint $table) {
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

            $table->index(['letter_id', 'from_position_id', 'to_position_id', 'received_at'], 'memos_letter_id_from_to_received_at_index');
            $table->index(['letter_id', 'received_at'], 'memos_letter_id_received_at_index');
            $table->index(['letter_recipient_id', 'received_at'], 'memos_letter_recipient_id_received_at_index');
            $table->index(['to_position_id', 'read_at'], 'memos_to_position_id_read_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memos');
    }
};

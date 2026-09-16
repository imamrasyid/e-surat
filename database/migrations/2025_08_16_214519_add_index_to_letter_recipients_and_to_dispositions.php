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
        Schema::table('letter_recipients', function (Blueprint $table) {
            $table->index(['letter_id', 'from_position_id', 'to_position_id', 'received_at'], 'letter_recipients_letter_id_from_to_received_at_index');
            $table->index(['letter_id', 'received_at'], 'letter_recipients_letter_id_received_at_index');
            $table->index(['letter_id', 'to_position_id', 'read_at'], 'letter_recipients_letter_id_to_read_at_index');

        });

        Schema::table('dispositions', function (Blueprint $table) {
            $table->index(['letter_id', 'from_position_id', 'to_position_id', 'received_at'], 'dispositions_letter_id_from_to_received_at_index');
            $table->index(['letter_id', 'received_at'], 'dispositions_letter_id_received_at_index');
            $table->index(['letter_recipient_id', 'received_at'], 'dispositions_letter_recipient_id_received_at_index');
            $table->index(['to_position_id', 'read_at'], 'dispositions_to_position_id_read_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letter_recipients', function (Blueprint $table) {
            $table->dropIndex('letter_recipients_letter_id_from_to_received_at_index');
            $table->dropIndex('letter_recipients_letter_id_received_at_index');
            $table->dropIndex('letter_recipients_letter_id_to_read_at_index');
        });

        Schema::table('dispositions', function (Blueprint $table) {
            $table->dropIndex('dispositions_letter_id_from_to_received_at_index');
            $table->dropIndex('dispositions_letter_id_received_at_index');
            $table->dropIndex('dispositions_letter_recipient_id_received_at_index');
            $table->dropIndex('dispositions_to_position_id_read_at_index');
        });
    }
};

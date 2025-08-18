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
        Schema::create('model_has_special_dispositions', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->unsignedBigInteger('to_position_id');
            $table->boolean('is_active')->default(true);
            $table->timestamp('granted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->unique(['model_type', 'model_id', 'to_position_id'], 'unique_special_dispositions');

            $table->index(['model_id', 'is_active'], 'index_model_special_dispositions');
            $table->index(['to_position_id', 'is_active'], 'index_position_special_dispositions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_has_special_dispositions');
    }
};

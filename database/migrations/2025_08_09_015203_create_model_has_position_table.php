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
        Schema::create('model_has_position', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('position_id');
            $table->unsignedBigInteger('model_id'); // ID user
            $table->string('model_type'); // Class user (untuk polymorphic)
            $table->date('start_date')->nullable(); // mulai menjabat
            $table->date('end_date')->nullable();   // selesai menjabat
            $table->boolean('is_active')->default(true); // jabatan saat ini atau tidak
            $table->timestamps();

            $table->index(['model_id', 'model_type'], 'model_has_position_model_index');
            $table->foreign('position_id')->references('id')->on('positions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_has_position');
    }
};

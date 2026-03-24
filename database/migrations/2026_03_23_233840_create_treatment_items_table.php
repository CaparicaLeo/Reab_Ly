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
        Schema::create('treatment_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('treatment_id')->onDelete('cascade');
            $table->integer('sets')->nullable();
            $table->integer('repetitions')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->text('frequency_text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treatment_items');
    }
};

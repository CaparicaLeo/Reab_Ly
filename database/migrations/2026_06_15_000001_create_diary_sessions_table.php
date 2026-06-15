<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diary_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('treatment_item_id')->constrained()->onDelete('cascade');
            $table->date('session_date');
            $table->boolean('completed')->default(false);
            $table->unsignedTinyInteger('pain_level')->nullable();
            $table->unsignedTinyInteger('fatigue_level')->nullable();
            $table->unsignedTinyInteger('difficulty_level')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'session_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diary_sessions');
    }
};

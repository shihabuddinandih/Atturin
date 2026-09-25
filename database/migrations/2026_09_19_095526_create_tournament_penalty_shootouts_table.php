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
        Schema::create('tournament_penalty_shootouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_match_id')->unique()->constrained('tournament_matches')->onDelete('cascade');
            $table->integer('skor_penalti_home');
            $table->integer('skor_penalti_away');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_penalty_shootouts');
    }
};

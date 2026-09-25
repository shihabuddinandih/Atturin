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
        Schema::create('tournament_match_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_match_id')->unique()->constrained('tournament_matches')->onDelete('cascade');
            $table->integer('possession_home')->nullable();
            $table->integer('possession_away')->nullable();
            $table->integer('tembakan_home')->default(0);
            $table->integer('tembakan_away')->default(0);
            $table->integer('tembakan_tepat_home')->default(0);
            $table->integer('tembakan_tepat_away')->default(0);
            $table->integer('pojok_home')->default(0);
            $table->integer('pojok_away')->default(0);
            $table->integer('pelanggaran_home')->default(0);
            $table->integer('pelanggaran_away')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_match_stats');
    }
};

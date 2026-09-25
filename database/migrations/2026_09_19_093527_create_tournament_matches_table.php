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
        Schema::create('tournament_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->onDelete('cascade');
            $table->foreignId('tournament_round_id')->constrained('tournament_rounds')->onDelete('cascade');
            $table->foreignId('home_slot_id')->nullable()->constrained('tournament_slots')->onDelete('set null');
            $table->foreignId('away_slot_id')->nullable()->constrained('tournament_slots')->onDelete('set null');
            $table->foreignId('team_home_id')->nullable()->constrained('teams')->onDelete('set null');
            $table->foreignId('team_away_id')->nullable()->constrained('teams')->onDelete('set null');
            $table->foreignId('home_source_match_id')->nullable()->constrained('tournament_matches')->onDelete('set null');
            $table->foreignId('away_source_match_id')->nullable()->constrained('tournament_matches')->onDelete('set null');
            $table->integer('bracket_position')->nullable();
            $table->date('jadwal_tanggal')->nullable();
            $table->time('jadwal_waktu')->nullable();
            $table->string('lokasi')->nullable();
            $table->string('status')->default('scheduled');
            $table->integer('skor_home')->default(0);
            $table->integer('skor_away')->default(0);
            $table->foreignId('pemenang_team_id')->nullable()->constrained('teams')->onDelete('set null');
            $table->string('menang_via')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_matches');
    }
};

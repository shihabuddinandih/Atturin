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
        Schema::table('tournament_matches', function (Blueprint $table) {
            $table->foreignId('home_jersey_id')->nullable()->after('team_home_id')->constrained('team_jerseys')->onDelete('set null');
            $table->foreignId('away_jersey_id')->nullable()->after('team_away_id')->constrained('team_jerseys')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournament_matches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('home_jersey_id');
            $table->dropConstrainedForeignId('away_jersey_id');
        });
    }
};

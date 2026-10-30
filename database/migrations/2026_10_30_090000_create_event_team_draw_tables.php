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
        Schema::table('events', function (Blueprint $table) {
            $table->json('team_draw_config')->nullable()->after('banner_image');
            $table->timestamp('team_draw_published_at')->nullable()->after('team_draw_config');
        });

        Schema::create('event_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->string('nama_tim');
            $table->string('warna', 20);
            $table->unsignedInteger('urutan');
            $table->timestamps();
        });

        Schema::create('event_team_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            // null = cadangan
            $table->foreignId('event_team_id')->nullable()->constrained('event_teams')->onDelete('cascade');
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->string('role_name')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'player_id']);
        });

        Schema::create('event_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->unsignedInteger('urutan');
            $table->unsignedInteger('putaran')->default(1);
            $table->foreignId('home_team_id')->constrained('event_teams')->onDelete('cascade');
            $table->foreignId('away_team_id')->constrained('event_teams')->onDelete('cascade');
            $table->time('perkiraan_mulai')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_matches');
        Schema::dropIfExists('event_team_players');
        Schema::dropIfExists('event_teams');

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['team_draw_config', 'team_draw_published_at']);
        });
    }
};

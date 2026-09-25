<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TournamentMatch extends Model
{
    protected $table = 'tournament_matches';

    protected $fillable = [
        'tournament_id',
        'tournament_round_id',
        'home_slot_id',
        'away_slot_id',
        'team_home_id',
        'team_away_id',
        'home_jersey_id',
        'away_jersey_id',
        'home_source_match_id',
        'away_source_match_id',
        'bracket_position',
        'jadwal_tanggal',
        'jadwal_waktu',
        'lokasi',
        'tournament_venue_id',
        'status',
        'skor_home',
        'skor_away',
        'pemenang_team_id',
        'menang_via',
        'started_at',
        'paused_at',
        'total_paused_seconds',
        'babak',
        'babak_started_at',
        'finished_at',
        'penalty_started_at',
    ];

    protected $casts = [
        'jadwal_tanggal' => 'date',
        'started_at' => 'datetime',
        'paused_at' => 'datetime',
        'babak' => 'integer',
        'babak_started_at' => 'datetime',
        'finished_at' => 'datetime',
        'penalty_started_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function round()
    {
        return $this->belongsTo(TournamentRound::class, 'tournament_round_id');
    }

    public function homeSlot()
    {
        return $this->belongsTo(TournamentSlot::class, 'home_slot_id');
    }

    public function awaySlot()
    {
        return $this->belongsTo(TournamentSlot::class, 'away_slot_id');
    }

    public function teamHome()
    {
        return $this->belongsTo(Team::class, 'team_home_id');
    }

    public function teamAway()
    {
        return $this->belongsTo(Team::class, 'team_away_id');
    }

    public function pemenang()
    {
        return $this->belongsTo(Team::class, 'pemenang_team_id');
    }

    public function homeJersey()
    {
        return $this->belongsTo(TeamJersey::class, 'home_jersey_id');
    }

    public function awayJersey()
    {
        return $this->belongsTo(TeamJersey::class, 'away_jersey_id');
    }

    public function venue()
    {
        return $this->belongsTo(TournamentVenue::class, 'tournament_venue_id');
    }

    public function homeSourceMatch()
    {
        return $this->belongsTo(TournamentMatch::class, 'home_source_match_id');
    }

    public function awaySourceMatch()
    {
        return $this->belongsTo(TournamentMatch::class, 'away_source_match_id');
    }

    public function events()
    {
        return $this->hasMany(TournamentMatchEvent::class)->orderBy('id');
    }

    public function stat()
    {
        return $this->hasOne(TournamentMatchStat::class);
    }

    public function penaltyShootout()
    {
        return $this->hasOne(TournamentPenaltyShootout::class);
    }

    public function penaltyKicks()
    {
        return $this->hasMany(TournamentPenaltyKick::class)->orderBy('urutan');
    }

    // ─── Helpers ──────────────────────────────────────────────────

    public function isReadyToSchedule(): bool
    {
        return $this->team_home_id !== null && $this->team_away_id !== null;
    }

    public function isInPenaltyShootout(): bool
    {
        return $this->penalty_started_at !== null;
    }

    public function isPaused(): bool
    {
        return $this->paused_at !== null;
    }

    /**
     * Name of the venue for this match, falling back to the free-text
     * `lokasi` on matches scheduled before venues existed.
     */
    public function venueName(): ?string
    {
        return $this->venue?->nama ?? $this->lokasi;
    }

    /**
     * Seconds of actual play elapsed since the current babak started,
     * excluding any paused time. Frozen at the pause point while paused,
     * and at finished_at once the match is over. Resets to 0 whenever the
     * babak advances (see MatchLiveService::advanceBabak).
     */
    public function elapsedSeconds(): int
    {
        // Matches already live before babak_started_at existed fall back to
        // their original kickoff time, so the clock self-heals without a
        // manual data backfill — it starts behaving normally the moment the
        // babak is next advanced.
        $reference = $this->babak_started_at ?? $this->started_at;

        if (! $reference) {
            return 0;
        }

        $end = $this->paused_at ?? ($this->status === 'finished' ? $this->finished_at : now());

        if (! $end) {
            return 0;
        }

        return max(0, $end->getTimestamp() - $reference->getTimestamp() - $this->total_paused_seconds);
    }

    public function elapsedMinutes(): int
    {
        return intdiv($this->elapsedSeconds(), 60) + 1;
    }
}

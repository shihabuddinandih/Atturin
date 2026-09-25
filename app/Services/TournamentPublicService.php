<?php

namespace App\Services;

use App\Enums\MatchEventType;
use App\Enums\MatchStatus;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use Illuminate\Support\Collection;

class TournamentPublicService
{
    /**
     * All matches that have an actual schedule (date/time set), in
     * chronological order — the public livescore page's full list, covering
     * upcoming, currently-live, and finished-with-score matches alike.
     */
    public function scheduledMatches(Tournament $tournament): Collection
    {
        return TournamentMatch::where('tournament_id', $tournament->id)
            ->whereNotNull('jadwal_tanggal')
            ->with(['round', 'teamHome', 'teamAway', 'pemenang', 'homeJersey', 'awayJersey', 'venue'])
            ->orderBy('jadwal_tanggal')
            ->orderBy('jadwal_waktu')
            ->get();
    }

    /**
     * Lightweight per-match state for polling the livescore list — just
     * enough to patch a live row's score/clock in place without reloading.
     */
    public function buildScheduledListPayload(Tournament $tournament): array
    {
        return $this->scheduledMatches($tournament)->map(fn ($match) => [
            'id' => $match->id,
            'status' => $match->status,
            'skor_home' => $match->skor_home,
            'skor_away' => $match->skor_away,
            'babak' => $match->babak,
            'elapsed_seconds' => $match->status === 'live' ? $match->elapsedSeconds() : null,
            'is_paused' => $match->isPaused(),
        ])->values()->all();
    }

    /**
     * Build a public-safe JSON payload for a single match — deliberately
     * excludes admin-only fields (who recorded an event, internal ids
     * beyond what's needed to render).
     */
    public function buildMatchPayload(TournamentMatch $match): array
    {
        $match->loadMissing(['round', 'teamHome', 'teamAway', 'pemenang', 'homeJersey', 'awayJersey', 'venue', 'events.team', 'events.player', 'stat', 'penaltyShootout', 'penaltyKicks.team']);

        return [
            'id' => $match->id,
            'round_nama' => $match->round->nama_ronde,
            'status' => $match->status,
            'status_label' => MatchStatus::from($match->status)->label(),
            'team_home' => $match->teamHome?->nama_tim,
            'team_away' => $match->teamAway?->nama_tim,
            'home_jersey' => $match->homeJersey ? ['nama' => $match->homeJersey->nama, 'warna' => $match->homeJersey->warna] : null,
            'away_jersey' => $match->awayJersey ? ['nama' => $match->awayJersey->nama, 'warna' => $match->awayJersey->warna] : null,
            'skor_home' => $match->skor_home,
            'skor_away' => $match->skor_away,
            'babak' => $match->babak,
            'elapsed_seconds' => $match->status === 'live' ? $match->elapsedSeconds() : null,
            'is_paused' => $match->isPaused(),
            'jadwal_tanggal' => $match->jadwal_tanggal?->format('Y-m-d'),
            'jadwal_waktu' => $match->jadwal_waktu,
            'lokasi' => $match->venueName(),
            'pemenang' => $match->pemenang?->nama_tim,
            'menang_via' => $match->menang_via,
            'penalti' => $match->penaltyShootout ? [
                'skor_home' => $match->penaltyShootout->skor_penalti_home,
                'skor_away' => $match->penaltyShootout->skor_penalti_away,
            ] : null,
            'dalam_adu_penalti' => $match->isInPenaltyShootout(),
            'penalti_kicks' => $match->isInPenaltyShootout()
                ? $match->penaltyKicks->map(fn ($kick) => [
                    'urutan' => $kick->urutan,
                    'team_id' => $kick->team_id,
                    'sisi' => (int) $kick->team_id === (int) $match->team_home_id ? 'home' : 'away',
                    'hasil' => $kick->hasil,
                ])->values()
                : [],
            'events' => $match->events->map(fn ($event) => [
                'tipe' => $event->tipe,
                'tipe_label' => MatchEventType::from($event->tipe)->label(),
                'menit' => $event->menit,
                'babak' => $event->babak,
                'catatan' => $event->catatan,
                'team' => $event->team->nama_tim,
                'pemain' => $event->player?->nama,
            ])->values(),
            'stat' => $match->stat ? [
                'possession_home' => $match->stat->possession_home,
                'possession_away' => $match->stat->possession_away,
                'tembakan_home' => $match->stat->tembakan_home,
                'tembakan_away' => $match->stat->tembakan_away,
                'tembakan_tepat_home' => $match->stat->tembakan_tepat_home,
                'tembakan_tepat_away' => $match->stat->tembakan_tepat_away,
                'pojok_home' => $match->stat->pojok_home,
                'pojok_away' => $match->stat->pojok_away,
                'pelanggaran_home' => $match->stat->pelanggaran_home,
                'pelanggaran_away' => $match->stat->pelanggaran_away,
            ] : null,
        ];
    }
}

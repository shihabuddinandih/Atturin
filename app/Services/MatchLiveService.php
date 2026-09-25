<?php

namespace App\Services;

use App\Enums\MatchEventType;
use App\Enums\PenaltyKickResult;
use App\Models\TeamJersey;
use App\Models\TournamentMatch;
use App\Models\TournamentMatchEvent;
use App\Models\TournamentMatchStat;
use App\Models\TournamentPenaltyKick;
use App\Models\TournamentPenaltyShootout;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MatchLiveService
{
    public function __construct(
        private BracketGeneratorService $bracketGenerator
    ) {}

    public function startMatch(TournamentMatch $match): void
    {
        if ($match->status !== 'scheduled') {
            throw new RuntimeException('Pertandingan hanya bisa dimulai dari status terjadwal.');
        }

        if (! $match->isReadyToSchedule()) {
            throw new RuntimeException('Kedua tim belum lengkap.');
        }

        $match->status = 'live';
        $match->started_at = now();
        $match->babak = 1;
        $match->babak_started_at = now();
        $match->save();
    }

    public function advanceBabak(TournamentMatch $match): void
    {
        if ($match->status !== 'live') {
            throw new RuntimeException('Pertandingan sedang tidak berlangsung.');
        }

        $match->babak += 1;
        $match->babak_started_at = now();
        $match->paused_at = null;
        $match->total_paused_seconds = 0;
        $match->save();
    }

    public function recordEvent(TournamentMatch $match, array $data, ?int $recordedByUserId = null): TournamentMatchEvent
    {
        if ($match->status !== 'live') {
            throw new RuntimeException('Pertandingan sedang tidak berlangsung.');
        }

        if (! in_array((int) $data['team_id'], [(int) $match->team_home_id, (int) $match->team_away_id], true)) {
            throw new RuntimeException('Tim tidak bertanding di pertandingan ini.');
        }

        $event = TournamentMatchEvent::create([
            'tournament_match_id' => $match->id,
            'team_id' => $data['team_id'],
            'team_player_id' => $data['team_player_id'] ?? null,
            'tipe' => $data['tipe'],
            'menit' => $data['menit'] ?? null,
            'babak' => $match->babak,
            'catatan' => $data['catatan'] ?? null,
            'dicatat_oleh' => $recordedByUserId,
        ]);

        $this->recomputeScore($match);

        return $event;
    }

    public function updateEvent(TournamentMatchEvent $event, array $data): void
    {
        $event->update([
            'team_id' => $data['team_id'],
            'team_player_id' => $data['team_player_id'] ?? null,
            'tipe' => $data['tipe'],
            'menit' => $data['menit'] ?? null,
            'catatan' => $data['catatan'] ?? null,
        ]);

        $this->recomputeScore($event->match);
    }

    public function deleteEvent(TournamentMatchEvent $event): void
    {
        $match = $event->match;
        $event->delete();
        $this->recomputeScore($match);
    }

    private function recomputeScore(TournamentMatch $match): void
    {
        $match->skor_home = TournamentMatchEvent::where('tournament_match_id', $match->id)
            ->where('team_id', $match->team_home_id)
            ->where('tipe', MatchEventType::GOAL->value)
            ->count();

        $match->skor_away = TournamentMatchEvent::where('tournament_match_id', $match->id)
            ->where('team_id', $match->team_away_id)
            ->where('tipe', MatchEventType::GOAL->value)
            ->count();

        $match->save();
    }

    public function updateStats(TournamentMatch $match, array $data): TournamentMatchStat
    {
        return TournamentMatchStat::updateOrCreate(
            ['tournament_match_id' => $match->id],
            $data
        );
    }

    public function pauseMatch(TournamentMatch $match): void
    {
        if ($match->status !== 'live') {
            throw new RuntimeException('Pertandingan sedang tidak berlangsung.');
        }

        if ($match->isPaused()) {
            throw new RuntimeException('Pertandingan sudah dijeda.');
        }

        $match->paused_at = now();
        $match->save();
    }

    public function resumeMatch(TournamentMatch $match): void
    {
        if (! $match->isPaused()) {
            throw new RuntimeException('Pertandingan sedang tidak dijeda.');
        }

        $match->total_paused_seconds += now()->getTimestamp() - $match->paused_at->getTimestamp();
        $match->paused_at = null;
        $match->save();
    }

    public function setJerseys(TournamentMatch $match, ?int $homeJerseyId, ?int $awayJerseyId): void
    {
        if ($homeJerseyId !== null && ! TeamJersey::where('id', $homeJerseyId)->where('team_id', $match->team_home_id)->exists()) {
            throw new RuntimeException('Jersey tidak sesuai dengan tim home.');
        }

        if ($awayJerseyId !== null && ! TeamJersey::where('id', $awayJerseyId)->where('team_id', $match->team_away_id)->exists()) {
            throw new RuntimeException('Jersey tidak sesuai dengan tim away.');
        }

        $match->home_jersey_id = $homeJerseyId;
        $match->away_jersey_id = $awayJerseyId;
        $match->save();
    }

    public function startPenaltyShootout(TournamentMatch $match): void
    {
        if ($match->status !== 'live') {
            throw new RuntimeException('Pertandingan sedang tidak berlangsung.');
        }

        if ($match->isInPenaltyShootout()) {
            throw new RuntimeException('Adu penalti sudah dimulai.');
        }

        $match->penalty_started_at = now();
        $match->save();
    }

    public function recordPenaltyKick(TournamentMatch $match, array $data): TournamentPenaltyKick
    {
        if (! $match->isInPenaltyShootout()) {
            throw new RuntimeException('Adu penalti belum dimulai.');
        }

        if (! in_array((int) $data['team_id'], [(int) $match->team_home_id, (int) $match->team_away_id], true)) {
            throw new RuntimeException('Tim tidak bertanding di pertandingan ini.');
        }

        $nextUrutan = TournamentPenaltyKick::where('tournament_match_id', $match->id)->count() + 1;

        return TournamentPenaltyKick::create([
            'tournament_match_id' => $match->id,
            'team_id' => $data['team_id'],
            'hasil' => $data['hasil'],
            'urutan' => $nextUrutan,
        ]);
    }

    public function deletePenaltyKick(TournamentPenaltyKick $kick): void
    {
        $kick->delete();
    }

    private function tallyPenaltyKicks(TournamentMatch $match): array
    {
        $home = TournamentPenaltyKick::where('tournament_match_id', $match->id)
            ->where('team_id', $match->team_home_id)
            ->where('hasil', PenaltyKickResult::GOL->value)
            ->count();

        $away = TournamentPenaltyKick::where('tournament_match_id', $match->id)
            ->where('team_id', $match->team_away_id)
            ->where('hasil', PenaltyKickResult::GOL->value)
            ->count();

        return [$home, $away];
    }

    public function finishMatch(TournamentMatch $match): void
    {
        if ($match->status !== 'live') {
            throw new RuntimeException('Pertandingan sedang tidak berlangsung.');
        }

        DB::transaction(function () use ($match) {
            $requiresWinner = $match->round->tipe === 'knockout';
            $isDraw = (int) $match->skor_home === (int) $match->skor_away;

            if ($isDraw && $requiresWinner) {
                if (! $match->isInPenaltyShootout()) {
                    throw new RuntimeException('Mulai adu penalti dulu sebelum menyelesaikan pertandingan.');
                }

                [$penaltyHome, $penaltyAway] = $this->tallyPenaltyKicks($match);

                if ($penaltyHome === $penaltyAway) {
                    throw new RuntimeException('Adu penalti masih imbang, lanjutkan pencatatan tendangan.');
                }

                TournamentPenaltyShootout::updateOrCreate(
                    ['tournament_match_id' => $match->id],
                    ['skor_penalti_home' => $penaltyHome, 'skor_penalti_away' => $penaltyAway]
                );

                $match->pemenang_team_id = $penaltyHome > $penaltyAway ? $match->team_home_id : $match->team_away_id;
                $match->menang_via = 'adu_penalti';
            } elseif (! $isDraw) {
                $match->pemenang_team_id = (int) $match->skor_home > (int) $match->skor_away
                    ? $match->team_home_id
                    : $match->team_away_id;
                $match->menang_via = 'normal';
            }
            // Draw in a group-stage match: no pemenang_team_id, points-based standings apply.

            $match->status = 'finished';
            $match->finished_at = now();
            $match->save();

            if ($match->pemenang_team_id) {
                $this->bracketGenerator->progressWinner($match);
            }
        });
    }
}

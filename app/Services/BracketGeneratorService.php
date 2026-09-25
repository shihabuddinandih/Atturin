<?php

namespace App\Services;

use App\Enums\TournamentFormat;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\TournamentRound;
use App\Models\TournamentSlot;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BracketGeneratorService
{
    /**
     * Generate the empty bracket/schedule skeleton for a tournament based on
     * its format and jumlah_tim, before any team has been placed.
     */
    public function generateSkeleton(Tournament $tournament): void
    {
        DB::transaction(function () use ($tournament) {
            match (TournamentFormat::from($tournament->format)) {
                TournamentFormat::SINGLE_ELIMINATION => $this->generateSingleElimination($tournament),
                TournamentFormat::ROUND_ROBIN => $this->generateRoundRobin($tournament),
                TournamentFormat::GROUP_KNOCKOUT => $this->generateGroupKnockout($tournament),
            };
        });
    }

    /**
     * Place a team into a bracket slot (drag & drop), syncing every match
     * that references the slot and resolving byes/progression as needed.
     */
    public function assignTeamToSlot(TournamentSlot $slot, Team $team): void
    {
        if ((int) $team->tournament_id !== (int) $slot->tournament_id) {
            throw new RuntimeException('Tim tidak berasal dari turnamen ini.');
        }

        if ($slot->team_id !== null) {
            throw new RuntimeException('Slot ini sudah terisi. Kosongkan slot terlebih dahulu.');
        }

        $alreadyPlaced = TournamentSlot::where('tournament_id', $slot->tournament_id)
            ->where('team_id', $team->id)
            ->exists();

        if ($alreadyPlaced) {
            throw new RuntimeException('Tim ini sudah ditempatkan di slot lain.');
        }

        DB::transaction(function () use ($slot, $team) {
            $slot->update(['team_id' => $team->id]);

            $matches = TournamentMatch::where('home_slot_id', $slot->id)
                ->orWhere('away_slot_id', $slot->id)
                ->get();

            foreach ($matches as $match) {
                if ((int) $match->home_slot_id === (int) $slot->id) {
                    $match->team_home_id = $team->id;
                }
                if ((int) $match->away_slot_id === (int) $slot->id) {
                    $match->team_away_id = $team->id;
                }
                $match->save();

                $this->resolveByeIfApplicable($match->fresh());
            }
        });
    }

    /**
     * Mark an empty slot as a bye — the admin's explicit choice of which
     * position sits out, rather than something decided at generation time.
     */
    public function markSlotAsBye(TournamentSlot $slot): void
    {
        if ($slot->team_id !== null) {
            throw new RuntimeException('Slot ini sudah terisi tim. Kosongkan slot terlebih dahulu.');
        }

        if ($slot->is_bye) {
            throw new RuntimeException('Slot ini sudah ditandai bye.');
        }

        $matches = TournamentMatch::where('home_slot_id', $slot->id)
            ->orWhere('away_slot_id', $slot->id)
            ->get();

        foreach ($matches as $match) {
            $siblingSlot = (int) $match->home_slot_id === (int) $slot->id ? $match->awaySlot : $match->homeSlot;
            if ($siblingSlot && $siblingSlot->is_bye) {
                throw new RuntimeException('Kedua sisi pertandingan ini tidak boleh sama-sama bye.');
            }
        }

        $bracketSize = 2 ** (int) ceil(log(max((int) $slot->tournament->jumlah_tim, 2), 2));
        $byeQuota = $bracketSize - (int) $slot->tournament->jumlah_tim;
        $currentByeCount = TournamentSlot::where('tournament_id', $slot->tournament_id)
            ->whereNull('grup')
            ->where('is_bye', true)
            ->count();

        if ($currentByeCount >= $byeQuota) {
            throw new RuntimeException('Jumlah bye sudah mencapai batas maksimal (' . $byeQuota . ').');
        }

        DB::transaction(function () use ($slot, $matches) {
            $slot->update(['is_bye' => true]);

            foreach ($matches as $match) {
                $this->resolveByeIfApplicable($match->fresh());
            }
        });
    }

    /**
     * Undo a bye mark, for correction. Blocked once the match has already
     * been resolved (finished) from it, same guard as clearSlot.
     */
    public function unmarkSlotBye(TournamentSlot $slot): void
    {
        $matches = TournamentMatch::where('home_slot_id', $slot->id)
            ->orWhere('away_slot_id', $slot->id)
            ->get();

        foreach ($matches as $match) {
            if ($match->status !== 'scheduled') {
                throw new RuntimeException('Tidak bisa mengubah slot ini: pertandingan terkait sudah dimulai atau selesai.');
            }
        }

        $slot->update(['is_bye' => false]);
    }

    /**
     * Remove a team from a slot. Blocked once any dependent match has
     * started or finished, since that data would no longer be trustworthy.
     */
    public function clearSlot(TournamentSlot $slot): void
    {
        DB::transaction(function () use ($slot) {
            $matches = TournamentMatch::where('home_slot_id', $slot->id)
                ->orWhere('away_slot_id', $slot->id)
                ->get();

            foreach ($matches as $match) {
                if ($match->status !== 'scheduled') {
                    throw new RuntimeException('Tidak bisa mengubah slot ini: pertandingan terkait sudah dimulai atau selesai.');
                }
            }

            foreach ($matches as $match) {
                if ((int) $match->home_slot_id === (int) $slot->id) {
                    $match->team_home_id = null;
                }
                if ((int) $match->away_slot_id === (int) $slot->id) {
                    $match->team_away_id = null;
                }
                $match->save();
            }

            $slot->update(['team_id' => null]);
        });
    }

    /**
     * Propagate a match's winner into whichever downstream match references
     * it as a source (single elimination progression).
     */
    public function progressWinner(TournamentMatch $match): void
    {
        if (! $match->pemenang_team_id) {
            return;
        }

        $next = TournamentMatch::where('home_source_match_id', $match->id)
            ->orWhere('away_source_match_id', $match->id)
            ->first();

        if (! $next) {
            return;
        }

        if ((int) $next->home_source_match_id === (int) $match->id) {
            $next->team_home_id = $match->pemenang_team_id;
        }
        if ((int) $next->away_source_match_id === (int) $match->id) {
            $next->team_away_id = $match->pemenang_team_id;
        }
        $next->save();
    }

    // ─── Single elimination ──────────────────────────────────────

    private function generateSingleElimination(Tournament $tournament): void
    {
        $teamCount = (int) $tournament->jumlah_tim;
        $bracketSize = 2 ** (int) ceil(log(max($teamCount, 2), 2));
        $numRounds = (int) log($bracketSize, 2);

        // Every round-1 position gets a real slot — which ones end up "bye"
        // is entirely the admin's call later (markSlotAsBye), not decided
        // here. bracketSize - teamCount of them will eventually be marked.
        $slots = [];
        for ($i = 0; $i < $bracketSize; $i++) {
            $slots[$i] = TournamentSlot::create([
                'tournament_id' => $tournament->id,
                'grup' => null,
                'kode' => 'Slot ' . ($i + 1),
                'urutan' => $i + 1,
            ]);
        }

        $previousRoundMatches = [];

        for ($round = 0; $round < $numRounds; $round++) {
            $teamsInRound = (int) ($bracketSize / (2 ** $round));
            $matchesInRound = intdiv($teamsInRound, 2);

            $roundRecord = TournamentRound::create([
                'tournament_id' => $tournament->id,
                'tipe' => 'knockout',
                'nama_ronde' => $this->knockoutRoundName($teamsInRound),
                'urutan' => $round + 1,
                'grup' => null,
            ]);

            $currentRoundMatches = [];

            for ($m = 0; $m < $matchesInRound; $m++) {
                $data = [
                    'tournament_id' => $tournament->id,
                    'tournament_round_id' => $roundRecord->id,
                    'bracket_position' => $m,
                    'status' => 'scheduled',
                ];

                if ($round === 0) {
                    $data['home_slot_id'] = $slots[$m * 2]->id;
                    $data['away_slot_id'] = $slots[$m * 2 + 1]->id;
                } else {
                    $data['home_source_match_id'] = $previousRoundMatches[$m * 2]->id;
                    $data['away_source_match_id'] = $previousRoundMatches[$m * 2 + 1]->id;
                }

                $currentRoundMatches[$m] = TournamentMatch::create($data);
            }

            $previousRoundMatches = $currentRoundMatches;
        }
    }

    private function knockoutRoundName(int $teamsInRound): string
    {
        return match ($teamsInRound) {
            2 => 'Final',
            4 => 'Semi Final',
            8 => 'Perempat Final',
            16 => '16 Besar',
            32 => '32 Besar',
            default => $teamsInRound . ' Besar',
        };
    }

    // ─── Round robin ──────────────────────────────────────────────

    private function generateRoundRobin(Tournament $tournament): void
    {
        $groupCount = (int) ($tournament->konfigurasi['jumlah_grup'] ?? 1);
        $totalTeams = (int) $tournament->jumlah_tim;

        if ($groupCount <= 1) {
            $this->generateRoundRobinGroup($tournament, null, $totalTeams, 0);

            return;
        }

        $baseSize = intdiv($totalTeams, $groupCount);
        $remainder = $totalTeams % $groupCount;
        $slotOffset = 0;

        for ($g = 0; $g < $groupCount; $g++) {
            $label = chr(ord('A') + $g);
            $size = $baseSize + ($g < $remainder ? 1 : 0);
            $this->generateRoundRobinGroup($tournament, $label, $size, $slotOffset);
            $slotOffset += $size;
        }
    }

    private function generateGroupKnockout(Tournament $tournament): void
    {
        // Group stage only. Knockout matches are created later, once group
        // results are final, from standings — not from this skeleton step.
        $this->generateRoundRobin($tournament);
    }

    private function generateRoundRobinGroup(Tournament $tournament, ?string $groupLabel, int $size, int $slotOffset): void
    {
        $slots = [];
        for ($i = 0; $i < $size; $i++) {
            $slots[$i] = TournamentSlot::create([
                'tournament_id' => $tournament->id,
                'grup' => $groupLabel,
                'kode' => ($groupLabel ? "Grup {$groupLabel} - " : '') . 'Posisi ' . ($i + 1),
                'urutan' => $slotOffset + $i + 1,
            ]);
        }

        $ids = array_map(fn ($slot) => $slot->id, $slots);
        if (count($ids) % 2 !== 0) {
            $ids[] = null; // bye placeholder for odd team counts
        }

        $k = count($ids);
        if ($k < 2) {
            return;
        }

        $numRounds = $k - 1;
        $half = intdiv($k, 2);

        for ($round = 0; $round < $numRounds; $round++) {
            $roundRecord = TournamentRound::create([
                'tournament_id' => $tournament->id,
                'tipe' => 'group',
                'nama_ronde' => ($groupLabel ? "Grup {$groupLabel} - " : '') . 'Matchday ' . ($round + 1),
                'urutan' => $round + 1,
                'grup' => $groupLabel,
            ]);

            for ($i = 0; $i < $half; $i++) {
                $homeId = $ids[$i];
                $awayId = $ids[$k - 1 - $i];

                if ($homeId === null || $awayId === null) {
                    continue; // this position sits out (bye) this matchday
                }

                TournamentMatch::create([
                    'tournament_id' => $tournament->id,
                    'tournament_round_id' => $roundRecord->id,
                    'home_slot_id' => $homeId,
                    'away_slot_id' => $awayId,
                    'status' => 'scheduled',
                ]);
            }

            // Circle method rotation: keep position 0 fixed, rotate the rest.
            $last = array_pop($ids);
            array_splice($ids, 1, 0, [$last]);
        }
    }

    // ─── Bye resolution ───────────────────────────────────────────

    private function resolveByeIfApplicable(TournamentMatch $match): void
    {
        if ($match->status !== 'scheduled') {
            return;
        }

        // Only "entry" matches (built from slots, not from a previous match's
        // winner) can be byes — deeper rounds always need to be played out.
        $isEntryMatch = $match->home_source_match_id === null && $match->away_source_match_id === null;
        if (! $isEntryMatch) {
            return;
        }

        $homeIsBye = (bool) $match->homeSlot?->is_bye;
        $awayIsBye = (bool) $match->awaySlot?->is_bye;

        if ($homeIsBye && $awayIsBye) {
            return;
        }

        if ($homeIsBye && $match->team_away_id !== null) {
            $this->finishAsBye($match, $match->team_away_id);
        } elseif ($awayIsBye && $match->team_home_id !== null) {
            $this->finishAsBye($match, $match->team_home_id);
        }
    }

    private function finishAsBye(TournamentMatch $match, int $winnerTeamId): void
    {
        $match->status = 'finished';
        $match->pemenang_team_id = $winnerTeamId;
        $match->menang_via = 'normal';
        $match->finished_at = now();
        $match->save();

        $this->progressWinner($match);
    }
}

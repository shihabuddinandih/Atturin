<?php

namespace App\Http\Controllers\Admin\Tournament;

use App\Enums\PenaltyKickResult;
use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\TournamentPenaltyKick;
use App\Services\MatchLiveService;
use Illuminate\Http\Request;
use RuntimeException;

class MatchPenaltyKickController extends Controller
{
    public function __construct(
        private MatchLiveService $matchLiveService
    ) {}

    public function store(Request $request, Tournament $tournament, TournamentMatch $match)
    {
        $this->authorize('update', $tournament);

        if ((int) $match->tournament_id !== (int) $tournament->id) {
            abort(404);
        }

        $validated = $request->validate([
            'team_id' => 'required|integer|in:' . $match->team_home_id . ',' . $match->team_away_id,
            'hasil' => 'required|in:' . implode(',', array_column(PenaltyKickResult::cases(), 'value')),
        ]);

        try {
            $this->matchLiveService->recordPenaltyKick($match, $validated);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Tendangan berhasil dicatat.');
    }

    public function destroy(Tournament $tournament, TournamentMatch $match, TournamentPenaltyKick $kick)
    {
        $this->authorize('update', $tournament);

        if ((int) $kick->tournament_match_id !== (int) $match->id || (int) $match->tournament_id !== (int) $tournament->id) {
            abort(404);
        }

        $this->matchLiveService->deletePenaltyKick($kick);

        return back()->with('success', 'Tendangan berhasil dihapus.');
    }
}

<?php

namespace App\Http\Controllers\Admin\Tournament;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Services\MatchLiveService;
use Illuminate\Http\Request;

class MatchStatController extends Controller
{
    public function __construct(
        private MatchLiveService $matchLiveService
    ) {}

    public function update(Request $request, Tournament $tournament, TournamentMatch $match)
    {
        $this->authorize('update', $tournament);

        if ((int) $match->tournament_id !== (int) $tournament->id) {
            abort(404);
        }

        $validated = $request->validate([
            'possession_home' => 'nullable|integer|min:0|max:100',
            'possession_away' => 'nullable|integer|min:0|max:100',
            'tembakan_home' => 'sometimes|integer|min:0',
            'tembakan_away' => 'sometimes|integer|min:0',
            'tembakan_tepat_home' => 'sometimes|integer|min:0',
            'tembakan_tepat_away' => 'sometimes|integer|min:0',
            'pojok_home' => 'sometimes|integer|min:0',
            'pojok_away' => 'sometimes|integer|min:0',
            'pelanggaran_home' => 'sometimes|integer|min:0',
            'pelanggaran_away' => 'sometimes|integer|min:0',
        ]);

        $stat = $this->matchLiveService->updateStats($match, $validated);

        if ($request->expectsJson()) {
            return response()->json(['stat' => $stat]);
        }

        return back()->with('success', 'Statistik berhasil disimpan.');
    }
}

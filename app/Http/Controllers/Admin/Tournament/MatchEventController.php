<?php

namespace App\Http\Controllers\Admin\Tournament;

use App\Enums\MatchEventType;
use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\TournamentMatchEvent;
use App\Services\MatchLiveService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class MatchEventController extends Controller
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

        $validated = $this->validateEvent($request, $match);

        try {
            $this->matchLiveService->recordEvent($match, $validated, auth()->id());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Kejadian berhasil dicatat.');
    }

    public function update(Request $request, Tournament $tournament, TournamentMatch $match, TournamentMatchEvent $event)
    {
        $this->authorize('update', $tournament);

        if ((int) $event->tournament_match_id !== (int) $match->id || (int) $match->tournament_id !== (int) $tournament->id) {
            abort(404);
        }

        $validated = $this->validateEvent($request, $match);

        $this->matchLiveService->updateEvent($event, $validated);

        return back()->with('success', 'Kejadian berhasil diperbarui.');
    }

    public function destroy(Tournament $tournament, TournamentMatch $match, TournamentMatchEvent $event)
    {
        $this->authorize('update', $tournament);

        if ((int) $event->tournament_match_id !== (int) $match->id || (int) $match->tournament_id !== (int) $tournament->id) {
            abort(404);
        }

        $this->matchLiveService->deleteEvent($event);

        return back()->with('success', 'Kejadian berhasil dihapus.');
    }

    private function validateEvent(Request $request, TournamentMatch $match): array
    {
        $validated = $request->validate([
            'team_id' => 'required|integer|in:' . $match->team_home_id . ',' . $match->team_away_id,
            'team_player_id' => [
                'nullable',
                'integer',
                Rule::exists('team_players', 'id')->where('team_id', $request->input('team_id')),
            ],
            'tipe' => 'required|in:' . implode(',', array_column(MatchEventType::cases(), 'value')),
            'menit' => 'nullable|string|max:10',
            'catatan' => 'nullable|string|max:255',
        ]);

        return $validated;
    }
}

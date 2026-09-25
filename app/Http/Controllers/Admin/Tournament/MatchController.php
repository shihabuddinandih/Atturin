<?php

namespace App\Http\Controllers\Admin\Tournament;

use App\Enums\MatchEventType;
use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Services\MatchLiveService;
use Illuminate\Http\Request;
use RuntimeException;

class MatchController extends Controller
{
    public function __construct(
        private MatchLiveService $matchLiveService
    ) {}

    public function show(Tournament $tournament, TournamentMatch $match)
    {
        $this->authorize('view', $tournament);

        if ((int) $match->tournament_id !== (int) $tournament->id) {
            abort(404);
        }

        $match->load(['round', 'teamHome.players', 'teamAway.players', 'teamHome.jerseys', 'teamAway.jerseys', 'homeJersey', 'awayJersey', 'events.player', 'events.team', 'stat', 'penaltyShootout', 'penaltyKicks.team']);
        $eventTypes = MatchEventType::cases();

        return view('admin.tournaments.match-center', compact('tournament', 'match', 'eventTypes'));
    }

    public function start(Tournament $tournament, TournamentMatch $match)
    {
        $this->authorize('update', $tournament);

        if ((int) $match->tournament_id !== (int) $tournament->id) {
            abort(404);
        }

        try {
            $this->matchLiveService->startMatch($match);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.tournaments.matches.show', [$tournament, $match])->with('success', 'Pertandingan dimulai.');
    }

    public function startPenalty(Tournament $tournament, TournamentMatch $match)
    {
        $this->authorize('update', $tournament);

        if ((int) $match->tournament_id !== (int) $tournament->id) {
            abort(404);
        }

        try {
            $this->matchLiveService->startPenaltyShootout($match);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.tournaments.matches.show', [$tournament, $match])->with('success', 'Adu penalti dimulai.');
    }

    public function pause(Tournament $tournament, TournamentMatch $match)
    {
        $this->authorize('update', $tournament);

        if ((int) $match->tournament_id !== (int) $tournament->id) {
            abort(404);
        }

        try {
            $this->matchLiveService->pauseMatch($match);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Pertandingan dijeda.');
    }

    public function resume(Tournament $tournament, TournamentMatch $match)
    {
        $this->authorize('update', $tournament);

        if ((int) $match->tournament_id !== (int) $tournament->id) {
            abort(404);
        }

        try {
            $this->matchLiveService->resumeMatch($match);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Pertandingan dilanjutkan.');
    }

    public function advanceBabak(Tournament $tournament, TournamentMatch $match)
    {
        $this->authorize('update', $tournament);

        if ((int) $match->tournament_id !== (int) $tournament->id) {
            abort(404);
        }

        try {
            $this->matchLiveService->advanceBabak($match);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Lanjut ke babak berikutnya.');
    }

    public function setJersey(Request $request, Tournament $tournament, TournamentMatch $match)
    {
        $this->authorize('update', $tournament);

        if ((int) $match->tournament_id !== (int) $tournament->id) {
            abort(404);
        }

        $validated = $request->validate([
            'home_jersey_id' => 'nullable|integer|exists:team_jerseys,id',
            'away_jersey_id' => 'nullable|integer|exists:team_jerseys,id',
        ]);

        try {
            $this->matchLiveService->setJerseys($match, $validated['home_jersey_id'] ?? null, $validated['away_jersey_id'] ?? null);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Jersey berhasil disimpan.');
    }

    public function finish(Tournament $tournament, TournamentMatch $match)
    {
        $this->authorize('update', $tournament);

        if ((int) $match->tournament_id !== (int) $tournament->id) {
            abort(404);
        }

        try {
            $this->matchLiveService->finishMatch($match);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.tournaments.bracket.show', $tournament)->with('success', 'Pertandingan selesai.');
    }
}

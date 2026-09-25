<?php

namespace App\Http\Controllers\Admin\Tournament;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentSlot;
use App\Services\BracketGeneratorService;
use Illuminate\Http\Request;
use RuntimeException;

class BracketController extends Controller
{
    public function __construct(
        private BracketGeneratorService $bracketGenerator
    ) {}

    public function show(Tournament $tournament)
    {
        $this->authorize('view', $tournament);

        $rounds = $tournament->rounds()->with(['matches' => function ($query) {
            $query->with(['homeSlot.team', 'awaySlot.team', 'teamHome', 'teamAway', 'pemenang']);
        }])->get();

        $placedTeamIds = $tournament->slots()->whereNotNull('team_id')->pluck('team_id');
        $unplacedTeams = $tournament->teams()->whereNotIn('id', $placedTeamIds)->orderBy('nama_tim')->get();

        $groupedRounds = $rounds->groupBy('grup');

        return view('admin.tournaments.bracket', compact('tournament', 'groupedRounds', 'unplacedTeams'));
    }

    public function assignSlot(Request $request, Tournament $tournament, TournamentSlot $slot)
    {
        $this->authorize('update', $tournament);

        if ((int) $slot->tournament_id !== (int) $tournament->id) {
            abort(404);
        }

        $validated = $request->validate([
            'team_id' => 'required|integer|exists:teams,id',
        ]);

        $team = Team::where('tournament_id', $tournament->id)->findOrFail($validated['team_id']);

        try {
            $this->bracketGenerator->assignTeamToSlot($slot, $team);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Tim {$team->nama_tim} berhasil ditempatkan ke {$slot->kode}.");
    }

    public function clearSlot(Tournament $tournament, TournamentSlot $slot)
    {
        $this->authorize('update', $tournament);

        if ((int) $slot->tournament_id !== (int) $tournament->id) {
            abort(404);
        }

        try {
            $this->bracketGenerator->clearSlot($slot);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Slot berhasil dikosongkan.');
    }

    public function markBye(Tournament $tournament, TournamentSlot $slot)
    {
        $this->authorize('update', $tournament);

        if ((int) $slot->tournament_id !== (int) $tournament->id) {
            abort(404);
        }

        try {
            $this->bracketGenerator->markSlotAsBye($slot);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "{$slot->kode} berhasil ditandai sebagai bye.");
    }

    public function unmarkBye(Tournament $tournament, TournamentSlot $slot)
    {
        $this->authorize('update', $tournament);

        if ((int) $slot->tournament_id !== (int) $tournament->id) {
            abort(404);
        }

        try {
            $this->bracketGenerator->unmarkSlotBye($slot);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Penanda bye berhasil dibatalkan.');
    }
}

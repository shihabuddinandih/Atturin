<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Services\TournamentPublicService;

class TournamentBracketPublicController extends Controller
{
    public function __construct(
        private TournamentPublicService $publicService
    ) {}

    public function show(Tournament $tournament)
    {
        $rounds = $tournament->rounds()->with(['matches' => function ($query) {
            $query->with(['homeSlot.team', 'awaySlot.team', 'teamHome', 'teamAway', 'pemenang']);
        }])->get();

        $groupedRounds = $rounds->groupBy('grup');
        $scheduleByRound = $this->publicService->matchesGroupedByRound($tournament);

        return view('tournament.bracket', compact('tournament', 'groupedRounds', 'scheduleByRound'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Tournament;

class TournamentBracketPublicController extends Controller
{
    public function show(Tournament $tournament)
    {
        $rounds = $tournament->rounds()->with(['matches' => function ($query) {
            $query->with(['homeSlot.team', 'awaySlot.team', 'teamHome', 'teamAway', 'pemenang']);
        }])->get();

        $groupedRounds = $rounds->groupBy('grup');

        return view('tournament.bracket', compact('tournament', 'groupedRounds'));
    }
}

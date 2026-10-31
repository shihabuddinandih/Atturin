<?php

namespace App\Http\Controllers;

use App\Models\Event;

class EventTeamPublicController extends Controller
{
    public function show(string $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        abort_if($event->team_draw_published_at === null, 404);

        $event->load([
            'teams.members.player',
            'matches.homeTeam',
            'matches.awayTeam',
        ]);

        return view('player.teams.show', compact('event'));
    }
}

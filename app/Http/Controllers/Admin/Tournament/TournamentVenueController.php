<?php

namespace App\Http\Controllers\Admin\Tournament;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentVenue;
use Illuminate\Http\Request;

class TournamentVenueController extends Controller
{
    public function store(Request $request, Tournament $tournament)
    {
        $this->authorize('update', $tournament);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        $tournament->venues()->create($validated);

        return back()->with('success', 'Lapangan berhasil ditambahkan.');
    }

    public function update(Request $request, Tournament $tournament, TournamentVenue $venue)
    {
        $this->authorize('update', $tournament);

        if ((int) $venue->tournament_id !== (int) $tournament->id) {
            abort(404);
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        $venue->update($validated);

        return back()->with('success', 'Lapangan berhasil diperbarui.');
    }

    public function destroy(Tournament $tournament, TournamentVenue $venue)
    {
        $this->authorize('update', $tournament);

        if ((int) $venue->tournament_id !== (int) $tournament->id) {
            abort(404);
        }

        $venue->delete();

        return back()->with('success', 'Lapangan berhasil dihapus.');
    }
}

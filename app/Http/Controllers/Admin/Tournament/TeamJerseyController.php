<?php

namespace App\Http\Controllers\Admin\Tournament;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamJersey;
use App\Models\Tournament;
use Illuminate\Http\Request;

class TeamJerseyController extends Controller
{
    public function store(Request $request, Tournament $tournament, Team $team)
    {
        $this->authorize('update', $tournament);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'warna' => 'required|string|max:20',
        ]);

        $team->jerseys()->create($validated);

        return back()->with('success', 'Jersey berhasil ditambahkan.');
    }

    public function update(Request $request, Tournament $tournament, Team $team, TeamJersey $jersey)
    {
        $this->authorize('update', $tournament);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'warna' => 'required|string|max:20',
        ]);

        $jersey->update($validated);

        return back()->with('success', 'Jersey berhasil diperbarui.');
    }

    public function destroy(Tournament $tournament, Team $team, TeamJersey $jersey)
    {
        $this->authorize('update', $tournament);

        $jersey->delete();

        return back()->with('success', 'Jersey berhasil dihapus.');
    }
}

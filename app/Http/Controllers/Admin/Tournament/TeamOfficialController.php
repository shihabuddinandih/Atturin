<?php

namespace App\Http\Controllers\Admin\Tournament;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamOfficial;
use App\Models\Tournament;
use Illuminate\Http\Request;

class TeamOfficialController extends Controller
{
    public function store(Request $request, Tournament $tournament, Team $team)
    {
        $this->authorize('update', $tournament);

        if ($team->officials()->count() >= $tournament->jumlah_official_per_tim) {
            return back()->with('error', 'Jumlah official sudah mencapai batas (' . $tournament->jumlah_official_per_tim . ' official).');
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'peran' => 'required|string|max:255',
            'kontak' => 'nullable|string|max:255',
        ]);

        $team->officials()->create($validated);

        return back()->with('success', 'Official berhasil ditambahkan.');
    }

    public function update(Request $request, Tournament $tournament, Team $team, TeamOfficial $official)
    {
        $this->authorize('update', $tournament);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'peran' => 'required|string|max:255',
            'kontak' => 'nullable|string|max:255',
        ]);

        $official->update($validated);

        return back()->with('success', 'Official berhasil diperbarui.');
    }

    public function destroy(Tournament $tournament, Team $team, TeamOfficial $official)
    {
        $this->authorize('update', $tournament);

        $official->delete();

        return back()->with('success', 'Official berhasil dihapus.');
    }
}

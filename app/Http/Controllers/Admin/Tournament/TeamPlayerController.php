<?php

namespace App\Http\Controllers\Admin\Tournament;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamPlayer;
use App\Models\Tournament;
use Illuminate\Http\Request;

class TeamPlayerController extends Controller
{
    public function store(Request $request, Tournament $tournament, Team $team)
    {
        $this->authorize('update', $tournament);

        if ($team->players()->count() >= $tournament->jumlah_pemain_per_tim) {
            return back()->with('error', 'Jumlah pemain sudah mencapai batas (' . $tournament->jumlah_pemain_per_tim . ' pemain).');
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nomor_punggung' => 'nullable|integer|min:0',
            'posisi' => 'nullable|string|max:255',
        ]);

        $team->players()->create($validated);

        return back()->with('success', 'Pemain berhasil ditambahkan.');
    }

    public function update(Request $request, Tournament $tournament, Team $team, TeamPlayer $player)
    {
        $this->authorize('update', $tournament);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nomor_punggung' => 'nullable|integer|min:0',
            'posisi' => 'nullable|string|max:255',
        ]);

        $player->update($validated);

        return back()->with('success', 'Pemain berhasil diperbarui.');
    }

    public function destroy(Tournament $tournament, Team $team, TeamPlayer $player)
    {
        $this->authorize('update', $tournament);

        $player->delete();

        return back()->with('success', 'Pemain berhasil dihapus.');
    }
}

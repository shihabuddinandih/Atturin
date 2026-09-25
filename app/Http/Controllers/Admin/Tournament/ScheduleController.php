<?php

namespace App\Http\Controllers\Admin\Tournament;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Tournament $tournament)
    {
        $this->authorize('view', $tournament);

        $matches = TournamentMatch::where('tournament_id', $tournament->id)
            ->with(['round', 'teamHome', 'teamAway'])
            ->get()
            ->sortBy(fn ($match) => [$match->round->urutan, $match->bracket_position])
            ->groupBy(fn ($match) => $match->round->nama_ronde);

        return view('admin.tournaments.schedule', compact('tournament', 'matches'));
    }

    public function update(Request $request, Tournament $tournament, TournamentMatch $tournamentMatch)
    {
        $this->authorize('update', $tournament);

        if ((int) $tournamentMatch->tournament_id !== (int) $tournament->id) {
            abort(404);
        }

        if (! $tournamentMatch->isReadyToSchedule()) {
            return back()->with('error', 'Pertandingan ini belum bisa dijadwalkan karena tim belum lengkap.');
        }

        $validated = $request->validate([
            'jadwal_tanggal' => 'required|date',
            'jadwal_waktu' => 'required',
            'lokasi' => 'nullable|string|max:255',
        ]);

        $tournamentMatch->update($validated);

        return back()->with('success', 'Jadwal pertandingan berhasil disimpan.');
    }
}

<?php

namespace App\Http\Controllers\Admin\Tournament;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\Tournament;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(Tournament $tournament)
    {
        $this->authorize('view', $tournament);

        $teams = $tournament->teams()->withCount(['players', 'officials'])->orderBy('nama_tim')->get();

        return view('admin.tournaments.teams.index', compact('tournament', 'teams'));
    }

    public function create(Tournament $tournament)
    {
        $this->authorize('update', $tournament);

        if ($tournament->teams()->count() >= $tournament->jumlah_tim) {
            return back()->with('error', 'Jumlah tim sudah mencapai batas (' . $tournament->jumlah_tim . ' tim).');
        }

        return view('admin.tournaments.teams.create', compact('tournament'));
    }

    public function store(Request $request, Tournament $tournament)
    {
        $this->authorize('update', $tournament);

        if ($tournament->teams()->count() >= $tournament->jumlah_tim) {
            return back()->with('error', 'Jumlah tim sudah mencapai batas (' . $tournament->jumlah_tim . ' tim).');
        }

        $validated = $this->validateTeam($request);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('team-logos', 'public');
        }

        $tournament->teams()->create($validated);

        return redirect()->route('admin.tournaments.teams.index', $tournament)->with('success', 'Tim berhasil ditambahkan.');
    }

    public function edit(Tournament $tournament, Team $team)
    {
        $this->authorize('update', $tournament);

        $team->load(['players', 'officials', 'jerseys']);

        return view('admin.tournaments.teams.edit', compact('tournament', 'team'));
    }

    public function update(Request $request, Tournament $tournament, Team $team)
    {
        $this->authorize('update', $tournament);

        $validated = $this->validateTeam($request);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('team-logos', 'public');
        }

        $team->update($validated);

        return redirect()->route('admin.tournaments.teams.index', $tournament)->with('success', 'Tim berhasil diperbarui.');
    }

    public function destroy(Tournament $tournament, Team $team)
    {
        $this->authorize('update', $tournament);

        $team->delete();

        return redirect()->route('admin.tournaments.teams.index', $tournament)->with('success', 'Tim berhasil dihapus.');
    }

    private function validateTeam(Request $request): array
    {
        return $request->validate([
            'nama_tim' => 'required|string|max:255',
            'kontak_manajer' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:2048',
        ]);
    }
}

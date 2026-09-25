<?php

namespace App\Http\Controllers\Admin\Tournament;

use App\Enums\TournamentFormat;
use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Services\BracketGeneratorService;
use Illuminate\Http\Request;

class TournamentController extends Controller
{
    public function __construct(
        private BracketGeneratorService $bracketGenerator
    ) {}

    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $adminId = auth()->id();

        $tournaments = Tournament::query()
            ->forAdmin($adminId)
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->withCount('teams')
            ->latest('tanggal_mulai')
            ->paginate(15)
            ->withQueryString();

        return view('admin.tournaments.index', compact('tournaments', 'status'));
    }

    public function create()
    {
        return view('admin.tournaments.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateTournament($request);

        if ($request->hasFile('banner_image')) {
            $validated['banner_image'] = $request->file('banner_image')->store('tournament-banners', 'public');
        }

        $validated['admin_id'] = auth()->id();

        $tournament = Tournament::create($validated);

        $this->bracketGenerator->generateSkeleton($tournament);

        return redirect()->route('admin.tournaments.show', $tournament)->with('success', 'Turnamen berhasil dibuat. Bagan pertandingan sudah otomatis dibuat.');
    }

    public function show(Tournament $tournament)
    {
        $this->authorize('view', $tournament);

        $tournament->loadCount('teams');

        return view('admin.tournaments.show', compact('tournament'));
    }

    public function edit(Tournament $tournament)
    {
        $this->authorize('update', $tournament);

        return view('admin.tournaments.edit', compact('tournament'));
    }

    public function update(Request $request, Tournament $tournament)
    {
        $this->authorize('update', $tournament);

        $validated = $this->validateTournament($request, $tournament, forUpdate: true);

        if ($request->hasFile('banner_image')) {
            $validated['banner_image'] = $request->file('banner_image')->store('tournament-banners', 'public');
        }

        $tournament->update($validated);

        return redirect()->route('admin.tournaments.show', $tournament)->with('success', 'Turnamen berhasil diperbarui.');
    }

    public function destroy(Tournament $tournament)
    {
        $this->authorize('delete', $tournament);

        $tournament->delete();

        return redirect()->route('admin.tournaments.index')->with('success', 'Turnamen berhasil dihapus.');
    }

    private function validateTournament(Request $request, ?Tournament $tournament = null, bool $forUpdate = false): array
    {
        $rules = [
            'nama_turnamen' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jumlah_pemain_per_tim' => 'required|integer|min:1',
            'jumlah_official_per_tim' => 'required|integer|min:0',
            'banner_image' => 'nullable|image|max:2048',
        ];

        if (! $forUpdate) {
            // Format & jumlah tim determine the bracket skeleton generated at
            // creation time, so they're immutable afterwards (M2 scope).
            $rules['format'] = 'required|in:' . implode(',', array_column(TournamentFormat::cases(), 'value'));
            $rules['jumlah_tim'] = 'required|integer|min:2';
            $rules['jumlah_grup'] = 'nullable|integer|min:2';
        }

        $validated = $request->validate($rules);

        if (! $forUpdate) {
            $validated['konfigurasi'] = $request->filled('jumlah_grup')
                ? ['jumlah_grup' => (int) $validated['jumlah_grup']]
                : null;
            unset($validated['jumlah_grup']);
        }

        return $validated;
    }
}

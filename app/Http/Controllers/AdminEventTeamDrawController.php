<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\EventMatchScheduler;
use App\Services\TeamDrawService;
use Illuminate\Http\Request;
use RuntimeException;

class AdminEventTeamDrawController extends Controller
{
    public function __construct(
        private TeamDrawService $teamDraw,
        private EventMatchScheduler $scheduler
    ) {}

    public function show(Event $event)
    {
        $this->authorize('view', $event);

        $joinedPlayers = $event->players()->wherePivot('status_join', 'joined')->get();
        $joinedCount = $joinedPlayers->count();
        $hadirCount = $joinedPlayers->where('pivot.hadir', true)->count();
        $roleSummary = $joinedPlayers
            ->groupBy(fn ($player) => $player->pivot->role_name ?: 'Tanpa peran')
            ->map->count()
            ->sortDesc();

        $event->load([
            'teams.members.player',
            'matches.homeTeam',
            'matches.awayTeam',
        ]);

        $joinStatuses = $event->players()->pluck('event_player.status_join', 'players.id');

        $gamesPerTeam = [];
        foreach ($event->matches as $match) {
            $gamesPerTeam[$match->home_team_id] = ($gamesPerTeam[$match->home_team_id] ?? 0) + 1;
            $gamesPerTeam[$match->away_team_id] = ($gamesPerTeam[$match->away_team_id] ?? 0) + 1;
        }

        $config = $event->team_draw_config ?? [];
        $fairCounts = $this->scheduler->fairCounts(max(2, $event->teams->count()), 30);

        return view('admin.events.team-draw', compact(
            'event', 'joinedCount', 'hadirCount', 'roleSummary',
            'joinStatuses', 'gamesPerTeam', 'config', 'fairCounts'
        ));
    }

    public function draw(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'jumlah_tim' => 'required|integer|min:2|max:' . TeamDrawService::MAX_TEAMS,
            'hanya_hadir' => 'nullable|boolean',
        ], $this->messages(), [
            'jumlah_tim' => 'jumlah tim',
        ]);
        $validated['hanya_hadir'] = $request->boolean('hanya_hadir');

        try {
            $result = $this->teamDraw->draw($event, $validated);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $sizes = implode('/', array_map('count', $result['teams']));
        $message = 'Pemain berhasil diacak ke ' . count($result['teams']) . ' tim (' . $sizes . ' pemain).';

        return redirect()->route('admin.events.teamDraw.show', $event)->with('success', $message);
    }

    public function schedule(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'jumlah_pertandingan' => 'required|integer|min:1|max:' . EventMatchScheduler::MAX_MATCHES,
            'durasi_laga' => 'nullable|integer|min:1|max:600',
        ], $this->messages(), [
            'jumlah_pertandingan' => 'jumlah pertandingan',
            'durasi_laga' => 'durasi per laga',
        ]);

        try {
            $this->scheduler->schedule(
                $event,
                (int) $validated['jumlah_pertandingan'],
                isset($validated['durasi_laga']) ? (int) $validated['durasi_laga'] : null
            );
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.events.teamDraw.show', $event)
            ->with('success', $validated['jumlah_pertandingan'] . ' pertandingan berhasil dijadwalkan.');
    }

    public function publish(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        if (! $event->teams()->exists()) {
            return back()->with('error', 'Acak tim terlebih dahulu sebelum dipublikasikan.');
        }

        $publish = $request->boolean('publish');
        $event->update(['team_draw_published_at' => $publish ? now() : null]);

        return back()->with('success', $publish
            ? 'Pembagian tim & jadwal sekarang tampil di link publik.'
            : 'Pembagian tim & jadwal disembunyikan dari publik.');
    }

    private function messages(): array
    {
        return [
            'required' => 'Kolom :attribute wajib diisi.',
            'integer' => 'Kolom :attribute harus berupa bilangan bulat.',
            'min' => 'Kolom :attribute minimal :min.',
            'max' => 'Kolom :attribute maksimal :max.',
            'boolean' => 'Kolom :attribute tidak valid.',
        ];
    }

    public function reset(Event $event)
    {
        $this->authorize('update', $event);

        $this->teamDraw->reset($event);

        return redirect()->route('admin.events.teamDraw.show', $event)
            ->with('success', 'Hasil acak tim dan jadwal berhasil dihapus.');
    }
}

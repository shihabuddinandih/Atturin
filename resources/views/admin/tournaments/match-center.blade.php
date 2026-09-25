@extends('layouts.admin')

@section('content')
<div class="w-full max-w-4xl mx-auto space-y-6">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-2">
            <a href="{{ route('admin.tournaments.schedule.index', $tournament) }}" class="hover:text-brand-500 transition-colors">{{ $tournament->nama_turnamen }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-600">Match Center</span>
        </div>
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ $match->round->nama_ronde }}</p>
    </div>

    {{-- Scoreboard --}}
    <div class="pro-card p-6" id="match-clock-root"
         data-babak-started-at="{{ ($match->babak_started_at ?? $match->started_at)?->toIso8601String() }}"
         data-paused-at="{{ $match->paused_at?->toIso8601String() }}"
         data-total-paused-seconds="{{ $match->total_paused_seconds }}"
         data-status="{{ $match->status }}">
        <div class="flex items-center justify-between gap-4">
            <div class="flex-1 text-center">
                <p class="text-sm font-semibold text-gray-800">{{ $match->teamHome->nama_tim ?? '-' }}</p>
                @if($match->homeJersey)
                    <span class="inline-flex items-center gap-1 mt-1 text-[10px] text-gray-400">
                        <span class="w-2.5 h-2.5 rounded-full border border-gray-200" style="background-color: {{ $match->homeJersey->warna }}"></span>
                        {{ $match->homeJersey->nama }}
                    </span>
                @endif
            </div>
            <div class="text-3xl font-bold text-gray-900 px-6">{{ $match->skor_home }} &ndash; {{ $match->skor_away }}</div>
            <div class="flex-1 text-center">
                <p class="text-sm font-semibold text-gray-800">{{ $match->teamAway->nama_tim ?? '-' }}</p>
                @if($match->awayJersey)
                    <span class="inline-flex items-center gap-1 mt-1 text-[10px] text-gray-400">
                        <span class="w-2.5 h-2.5 rounded-full border border-gray-200" style="background-color: {{ $match->awayJersey->warna }}"></span>
                        {{ $match->awayJersey->nama }}
                    </span>
                @endif
            </div>
        </div>
        <div class="flex flex-col items-center gap-2 mt-3">
            <div class="flex items-center justify-center gap-2">
                <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600 uppercase tracking-wider">
                    {{ \App\Enums\MatchStatus::from($match->status)->label() }}
                </span>
                @if($match->status === 'live' && $match->isPaused())
                    <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 uppercase tracking-wider">Dijeda</span>
                @endif
            </div>
            @if($match->status === 'live')
                <div class="flex items-center justify-center gap-2">
                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold bg-brand-900 text-white uppercase tracking-wider">Babak {{ $match->babak }}</span>
                    <form action="{{ route('admin.tournaments.matches.babak.advance', [$tournament, $match]) }}" method="POST" onsubmit="return confirm('Lanjut ke babak berikutnya? Jam akan mulai dari 0:00 lagi.')">
                        @csrf
                        <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-bold border border-gray-200 text-gray-500 hover:border-brand-300 hover:text-brand-600 uppercase tracking-wider">Lanjut Babak</button>
                    </form>
                </div>
                <span id="match-clock" class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold bg-brand-50 text-brand-600 tabular-nums">00:00</span>
            @endif
        </div>

        @if($match->status === 'finished')
            <div class="mt-4 text-center text-sm text-gray-600">
                Pemenang: <span class="font-bold text-emerald-600">{{ $match->pemenang->nama_tim ?? 'Seri' }}</span>
                @if($match->menang_via === 'adu_penalti' && $match->penaltyShootout)
                    <span class="text-gray-400">(adu penalti {{ $match->penaltyShootout->skor_penalti_home }}-{{ $match->penaltyShootout->skor_penalti_away }})</span>
                @endif
            </div>
        @endif

        <div class="mt-5 flex justify-center">
            @if($match->status === 'scheduled')
                <form action="{{ route('admin.tournaments.matches.start', [$tournament, $match]) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-lime-400 hover:bg-lime-500 text-brand-900 font-bold text-sm shadow-lg shadow-lime-400/20">Mulai Pertandingan</button>
                </form>
            @elseif($match->status === 'live')
                <div class="flex flex-wrap items-center justify-center gap-3">
                    @if(! $match->isPaused())
                        <form action="{{ route('admin.tournaments.matches.pause', [$tournament, $match]) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-bold text-sm hover:border-amber-300 hover:text-amber-600">Pause</button>
                        </form>
                    @else
                        <form action="{{ route('admin.tournaments.matches.resume', [$tournament, $match]) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-5 py-2.5 rounded-xl border border-amber-300 text-amber-600 font-bold text-sm hover:bg-amber-50">Lanjutkan</button>
                        </form>
                    @endif
                    @if($match->round->tipe === 'knockout' && $match->skor_home === $match->skor_away && ! $match->isInPenaltyShootout())
                        <form action="{{ route('admin.tournaments.matches.penalty.start', [$tournament, $match]) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm shadow-lg shadow-amber-500/20">Lanjut Penalti</button>
                        </form>
                    @endif
                    <form action="{{ route('admin.tournaments.matches.finish', [$tournament, $match]) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-rose-500 hover:bg-rose-600 text-white font-bold text-sm shadow-lg shadow-rose-500/20">Selesaikan Pertandingan</button>
                    </form>
                </div>
                @if($match->round->tipe === 'knockout' && $match->skor_home === $match->skor_away && ! $match->isInPenaltyShootout())
                    <p class="text-center text-xs text-gray-400 mt-3">Skor imbang &mdash; klik "Lanjut Penalti" untuk mencatat adu penalti, atau langsung selesaikan kalau ternyata tidak jadi imbang.</p>
                @endif
            @endif
        </div>
    </div>

    {{-- Jersey selection --}}
    @if($match->status !== 'finished')
        <div class="pro-card p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Jersey yang Dipakai</h3>
            <form action="{{ route('admin.tournaments.matches.setJersey', [$tournament, $match]) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-500 mb-1">{{ $match->teamHome->nama_tim }}</label>
                        <select name="home_jersey_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                            <option value="">-</option>
                            @foreach($match->teamHome->jerseys as $jersey)
                                <option value="{{ $jersey->id }}" {{ (int) $match->home_jersey_id === $jersey->id ? 'selected' : '' }}>{{ $jersey->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-500 mb-1">{{ $match->teamAway->nama_tim }}</label>
                        <select name="away_jersey_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                            <option value="">-</option>
                            @foreach($match->teamAway->jerseys as $jersey)
                                <option value="{{ $jersey->id }}" {{ (int) $match->away_jersey_id === $jersey->id ? 'selected' : '' }}>{{ $jersey->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="mt-3 px-4 py-2 rounded-xl bg-brand-500 text-white text-sm font-semibold hover:bg-brand-600">Simpan Jersey</button>
            </form>
        </div>
    @endif

    {{-- Penalty shootout --}}
    @if($match->isInPenaltyShootout())
        @php
            $homeKicks = $match->penaltyKicks->where('team_id', $match->team_home_id)->values();
            $awayKicks = $match->penaltyKicks->where('team_id', $match->team_away_id)->values();
        @endphp
        <div class="pro-card p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4 text-center">Adu Penalti</h3>

            <div class="flex items-center justify-center gap-6 mb-3">
                <span class="text-2xl font-bold text-gray-900">{{ $homeKicks->where('hasil', 'gol')->count() }}</span>
                <span class="text-[10px] text-gray-400 uppercase tracking-wider">Skor Penalti</span>
                <span class="text-2xl font-bold text-gray-900">{{ $awayKicks->where('hasil', 'gol')->count() }}</span>
            </div>

            <div class="flex items-start justify-between gap-4 mb-5">
                <div class="flex-1">
                    <p class="text-xs font-semibold text-gray-600 mb-1.5">{{ $match->teamHome->nama_tim }}</p>
                    <div class="flex gap-1.5 flex-wrap">
                        @forelse($homeKicks as $kick)
                            <span class="w-3.5 h-3.5 rounded-full {{ $kick->hasil === 'gol' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                        @empty
                            <span class="text-xs text-gray-300 italic">Belum ada tendangan</span>
                        @endforelse
                    </div>
                </div>
                <div class="flex-1 text-right">
                    <p class="text-xs font-semibold text-gray-600 mb-1.5">{{ $match->teamAway->nama_tim }}</p>
                    <div class="flex gap-1.5 flex-wrap justify-end">
                        @forelse($awayKicks as $kick)
                            <span class="w-3.5 h-3.5 rounded-full {{ $kick->hasil === 'gol' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                        @empty
                            <span class="text-xs text-gray-300 italic">Belum ada tendangan</span>
                        @endforelse
                    </div>
                </div>
            </div>

            @if($match->status === 'live')
                @php
                    $penaltySides = [
                        ['label' => $match->teamHome->nama_tim, 'team_id' => $match->team_home_id],
                        ['label' => $match->teamAway->nama_tim, 'team_id' => $match->team_away_id],
                    ];
                @endphp
                <div class="grid grid-cols-2 gap-4">
                    @foreach($penaltySides as $side)
                        <div class="text-center">
                            <p class="text-xs font-semibold text-gray-600 mb-2">{{ $side['label'] }}</p>
                            <div class="flex gap-2 justify-center">
                                <form action="{{ route('admin.tournaments.matches.penalty.kicks.store', [$tournament, $match]) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="team_id" value="{{ $side['team_id'] }}">
                                    <input type="hidden" name="hasil" value="gol">
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-semibold hover:bg-emerald-100">+ Gol</button>
                                </form>
                                <form action="{{ route('admin.tournaments.matches.penalty.kicks.store', [$tournament, $match]) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="team_id" value="{{ $side['team_id'] }}">
                                    <input type="hidden" name="hasil" value="gagal">
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 text-xs font-semibold hover:bg-rose-100">+ Gagal</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($match->penaltyKicks->isNotEmpty())
                <div class="mt-5 border-t border-gray-100 divide-y divide-gray-50">
                    @foreach($match->penaltyKicks as $kick)
                        <div class="py-2 flex items-center justify-between text-xs">
                            <span class="text-gray-600">#{{ $kick->urutan }} &mdash; {{ $kick->team->nama_tim }} &mdash; <span class="font-semibold {{ $kick->hasil === 'gol' ? 'text-emerald-600' : 'text-rose-600' }}">{{ \App\Enums\PenaltyKickResult::from($kick->hasil)->label() }}</span></span>
                            @if($match->status === 'live')
                                <form action="{{ route('admin.tournaments.matches.penalty.kicks.destroy', [$tournament, $match, $kick]) }}" method="POST" onsubmit="return confirm('Hapus tendangan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-semibold text-rose-500 hover:text-rose-700">Hapus</button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @if($match->status === 'live')
        {{-- Event entry --}}
        <div class="pro-card p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Catat Kejadian</h3>
            <form action="{{ route('admin.tournaments.matches.events.store', [$tournament, $match]) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-5 gap-3 items-end">
                @csrf
                <div>
                    <label class="block text-[10px] font-semibold text-gray-500 mb-1">Tim</label>
                    <select name="team_id" id="event-team" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        <option value="{{ $match->team_home_id }}">{{ $match->teamHome->nama_tim }}</option>
                        <option value="{{ $match->team_away_id }}">{{ $match->teamAway->nama_tim }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-gray-500 mb-1">Jenis</label>
                    <select name="tipe" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        @foreach($eventTypes as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-gray-500 mb-1">Pemain</label>
                    <select name="team_player_id" id="event-player" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        <option value="">-</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-gray-500 mb-1">Menit</label>
                    <input type="text" name="menit" id="event-menit" placeholder="45'" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <button type="submit" class="px-4 py-2 rounded-xl bg-brand-500 text-white text-sm font-semibold hover:bg-brand-600">+ Tambah</button>
            </form>
        </div>
    @endif

    {{-- Event log --}}
    @if($match->events->isNotEmpty())
        <div class="pro-card overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Log Kejadian</h3>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($match->events as $event)
                    <div class="px-5 py-3 flex items-center justify-between gap-3 text-sm">
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mr-1">Babak {{ $event->babak ?? '-' }}</span>
                            <span class="font-semibold text-gray-800">{{ $event->menit ?? '-' }}</span>
                            <span class="text-gray-600">{{ \App\Enums\MatchEventType::from($event->tipe)->label() }}</span>
                            &mdash; {{ $event->player->nama ?? $event->team->nama_tim }}
                            <span class="text-gray-400">({{ $event->team->nama_tim }})</span>
                        </div>
                        @if($match->status === 'live')
                            <form action="{{ route('admin.tournaments.matches.events.destroy', [$tournament, $match, $event]) }}" method="POST" onsubmit="return confirm('Hapus kejadian ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-rose-500 hover:text-rose-700">Hapus</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Foul --}}
    @if($match->status !== 'scheduled')
        @php $stat = $match->stat; @endphp
        <div class="pro-card p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4 text-center">Foul</h3>
            <div class="flex items-center justify-center gap-8" id="stat-panel" data-url="{{ route('admin.tournaments.matches.stats.update', [$tournament, $match]) }}">
                <div class="flex items-center gap-3">
                    <button type="button" class="stat-btn w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200 font-bold" data-field="pelanggaran_home" data-delta="-1" {{ $match->status !== 'live' ? 'disabled' : '' }}>&minus;</button>
                    <span class="stat-value w-10 text-center text-2xl font-bold text-gray-900" data-field="pelanggaran_home">{{ $stat?->pelanggaran_home ?? 0 }}</span>
                    <button type="button" class="stat-btn w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200 font-bold" data-field="pelanggaran_home" data-delta="1" {{ $match->status !== 'live' ? 'disabled' : '' }}>&plus;</button>
                </div>
                <span class="text-xs font-semibold text-gray-400 uppercase">vs</span>
                <div class="flex items-center gap-3">
                    <button type="button" class="stat-btn w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200 font-bold" data-field="pelanggaran_away" data-delta="-1" {{ $match->status !== 'live' ? 'disabled' : '' }}>&minus;</button>
                    <span class="stat-value w-10 text-center text-2xl font-bold text-gray-900" data-field="pelanggaran_away">{{ $stat?->pelanggaran_away ?? 0 }}</span>
                    <button type="button" class="stat-btn w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200 font-bold" data-field="pelanggaran_away" data-delta="1" {{ $match->status !== 'live' ? 'disabled' : '' }}>&plus;</button>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    (function () {
        // Live match clock: ticks every second from started_at, minus any
        // paused time, and auto-fills the "Menit" event field to match.
        const clockRoot = document.getElementById('match-clock-root');
        if (clockRoot && clockRoot.dataset.babakStartedAt) {
            const startedAt = new Date(clockRoot.dataset.babakStartedAt).getTime();
            const totalPausedSeconds = parseInt(clockRoot.dataset.totalPausedSeconds || '0', 10);
            const pausedAt = clockRoot.dataset.pausedAt ? new Date(clockRoot.dataset.pausedAt).getTime() : null;
            const clockEl = document.getElementById('match-clock');
            const menitInput = document.getElementById('event-menit');

            function elapsedSeconds() {
                const end = pausedAt ?? Date.now();
                return Math.max(0, Math.floor((end - startedAt) / 1000) - totalPausedSeconds);
            }

            function tick() {
                const seconds = elapsedSeconds();
                const mm = String(Math.floor(seconds / 60)).padStart(2, '0');
                const ss = String(seconds % 60).padStart(2, '0');
                if (clockEl) clockEl.textContent = `${mm}:${ss}`;

                if (menitInput && document.activeElement !== menitInput) {
                    menitInput.value = Math.floor(seconds / 60) + 1 + "'";
                }
            }

            tick();
            if (!pausedAt) {
                setInterval(tick, 1000);
            }
        }

        const rosters = {
            {{ $match->team_home_id }}: @json($match->teamHome->players->map(fn($p) => ['id' => $p->id, 'nama' => $p->nama])),
            {{ $match->team_away_id }}: @json($match->teamAway->players->map(fn($p) => ['id' => $p->id, 'nama' => $p->nama])),
        };

        const teamSelect = document.getElementById('event-team');
        const playerSelect = document.getElementById('event-player');

        function syncPlayers() {
            if (!teamSelect || !playerSelect) return;
            const players = rosters[teamSelect.value] || [];
            playerSelect.innerHTML = '<option value="">-</option>' + players.map((p) => `<option value="${p.id}">${p.nama}</option>`).join('');
        }

        if (teamSelect) {
            teamSelect.addEventListener('change', syncPlayers);
            syncPlayers();
        }

        // Stat steppers (auto-save on click)
        const statPanel = document.getElementById('stat-panel');
        if (statPanel) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const url = statPanel.dataset.url;

            function saveStat(field, value) {
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-HTTP-Method-Override': 'PATCH',
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: '_method=PATCH&' + field + '=' + encodeURIComponent(value),
                }).catch(() => alert('Gagal menyimpan statistik.'));
            }

            statPanel.querySelectorAll('.stat-btn').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const field = btn.dataset.field;
                    const delta = parseInt(btn.dataset.delta, 10);
                    const valueEl = statPanel.querySelector(`.stat-value[data-field="${field}"]`);
                    const newValue = Math.max(0, parseInt(valueEl.textContent, 10) + delta);
                    valueEl.textContent = newValue;
                    saveStat(field, newValue);
                });
            });

            statPanel.querySelectorAll('.stat-input').forEach((input) => {
                input.addEventListener('change', () => {
                    saveStat(input.dataset.field, input.value);
                });
            });
        }
    })();
</script>
@endpush

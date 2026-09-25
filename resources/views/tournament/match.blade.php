@extends('tournament.layout')

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('tournament.show', $tournament) }}" class="text-xs text-gray-400 hover:text-brand-500">&larr; Kembali ke Livescore</a>
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mt-2">{{ $payload['round_nama'] }}</p>
    </div>

    <div id="match-root" data-poll-url="{{ route('tournament.match.poll', [$tournament, $match]) }}" data-status="{{ $payload['status'] }}" data-in-penalty="{{ ($payload['dalam_adu_penalti'] ?? false) ? 1 : 0 }}" data-kick-count="{{ count($payload['penalti_kicks'] ?? []) }}"
         data-elapsed-seconds="{{ $payload['elapsed_seconds'] ?? 0 }}" data-is-paused="{{ ($payload['is_paused'] ?? false) ? 1 : 0 }}" data-babak="{{ $payload['babak'] ?? 1 }}">
        <div class="pro-card p-6">
            <div class="flex items-center justify-center gap-2 mb-1">
                <span id="status-badge" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $payload['status'] === 'live' ? 'bg-rose-100 text-rose-700' : ($payload['status'] === 'finished' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600') }}">
                    @if($payload['status'] === 'live')
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 live-dot"></span>
                    @endif
                    {{ $payload['status_label'] }}
                </span>
            </div>
            @if($payload['status'] === 'live')
                <div class="flex flex-col items-center gap-1 mb-1">
                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold bg-brand-900 text-white uppercase tracking-wider">Babak {{ $payload['babak'] ?? 1 }}</span>
                    <span id="match-clock" class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold bg-brand-50 text-brand-600 tabular-nums">00:00</span>
                </div>
            @endif

            <div class="flex items-center justify-between gap-4 mt-4">
                <div class="flex-1 text-center">
                    <p class="text-sm font-semibold text-gray-800">{{ $payload['team_home'] ?? '-' }}</p>
                    @if($payload['home_jersey'])
                        <span class="inline-flex items-center gap-1 mt-1 text-[10px] text-gray-400">
                            <span class="w-2.5 h-2.5 rounded-full border border-gray-200" style="background-color: {{ $payload['home_jersey']['warna'] }}"></span>
                            {{ $payload['home_jersey']['nama'] }}
                        </span>
                    @endif
                </div>
                <div id="score" class="text-3xl font-bold text-gray-900 px-6">{{ $payload['skor_home'] }} &ndash; {{ $payload['skor_away'] }}</div>
                <div class="flex-1 text-center">
                    <p class="text-sm font-semibold text-gray-800">{{ $payload['team_away'] ?? '-' }}</p>
                    @if($payload['away_jersey'])
                        <span class="inline-flex items-center gap-1 mt-1 text-[10px] text-gray-400">
                            <span class="w-2.5 h-2.5 rounded-full border border-gray-200" style="background-color: {{ $payload['away_jersey']['warna'] }}"></span>
                            {{ $payload['away_jersey']['nama'] }}
                        </span>
                    @endif
                </div>
            </div>

            @if($payload['lokasi'] || $payload['jadwal_tanggal'])
                <p class="text-center text-xs text-gray-400 mt-3">
                    @if($payload['jadwal_tanggal']) {{ \Carbon\Carbon::parse($payload['jadwal_tanggal'])->translatedFormat('d M Y') }} @endif
                    @if($payload['jadwal_waktu']) &middot; {{ \Carbon\Carbon::parse($payload['jadwal_waktu'])->format('H:i') }} WIB @endif
                    @if($payload['lokasi']) &middot; {{ $payload['lokasi'] }} @endif
                </p>
            @endif

            @if($payload['status'] === 'finished')
                <div class="text-center mt-3 text-sm text-gray-600">
                    Pemenang: <span class="font-bold text-emerald-600">{{ $payload['pemenang'] ?? 'Seri' }}</span>
                </div>
            @endif
        </div>

        @if($payload['dalam_adu_penalti'] ?? false)
            @include('tournament.partials.penalty-widget', ['payload' => $payload])
        @endif

        {{-- Always rendered while live — even with zero data — so polling
             always has somewhere to write updates without a page reload. --}}
        @if($payload['status'] === 'live')
            <div class="pro-card p-6 mt-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3 text-center">Foul</h3>
                <div id="stat-list" class="flex items-center justify-center gap-8">
                    <span class="stat-home text-2xl font-bold text-gray-900" data-field="pelanggaran_home">{{ $payload['stat']['pelanggaran_home'] ?? 0 }}</span>
                    <span class="text-xs font-semibold text-gray-400 uppercase">vs</span>
                    <span class="stat-away text-2xl font-bold text-gray-900" data-field="pelanggaran_away">{{ $payload['stat']['pelanggaran_away'] ?? 0 }}</span>
                </div>
            </div>

            <div class="pro-card p-6 mt-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Jalannya Pertandingan</h3>
                <div id="events-list" class="space-y-2 text-sm">
                    @forelse($payload['events'] as $event)
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">B{{ $event['babak'] ?? '-' }}</span>
                            <span class="font-semibold text-gray-700 w-10">{{ $event['menit'] ?? '-' }}</span>
                            <span class="text-gray-600">{{ $event['tipe_label'] }}</span>
                            <span class="text-gray-400">&mdash; {{ $event['pemain'] ?? $event['team'] }} ({{ $event['team'] }})</span>
                        </div>
                    @empty
                        <p id="events-empty" class="text-gray-400 text-xs">Belum ada kejadian.</p>
                    @endforelse
                </div>
            </div>
        @endif

        @if($payload['status'] === 'finished' && $payload['stat'])
            <div class="pro-card p-6 mt-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3 text-center">Foul</h3>
                <div class="flex items-center justify-center gap-8">
                    <span class="text-2xl font-bold text-gray-900">{{ $payload['stat']['pelanggaran_home'] ?? 0 }}</span>
                    <span class="text-xs font-semibold text-gray-400 uppercase">vs</span>
                    <span class="text-2xl font-bold text-gray-900">{{ $payload['stat']['pelanggaran_away'] ?? 0 }}</span>
                </div>
            </div>
        @endif

        @if($payload['status'] === 'finished' && $payload['events']->isNotEmpty())
            <div class="pro-card p-6 mt-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Jalannya Pertandingan</h3>
                <div class="space-y-2 text-sm">
                    @foreach($payload['events'] as $event)
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">B{{ $event['babak'] ?? '-' }}</span>
                            <span class="font-semibold text-gray-700 w-10">{{ $event['menit'] ?? '-' }}</span>
                            <span class="text-gray-600">{{ $event['tipe_label'] }}</span>
                            <span class="text-gray-400">&mdash; {{ $event['pemain'] ?? $event['team'] }} ({{ $event['team'] }})</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const root = document.getElementById('match-root');
        if (!root) return;

        let currentStatus = root.dataset.status;
        let currentInPenalty = root.dataset.inPenalty === '1';
        let currentBabak = parseInt(root.dataset.babak || '1', 10);
        let lastKickCount = parseInt(root.dataset.kickCount || '0', 10);
        let elapsedSeconds = parseInt(root.dataset.elapsedSeconds || '0', 10);
        let isPaused = root.dataset.isPaused === '1';

        const clockEl = document.getElementById('match-clock');
        function renderClock() {
            if (!clockEl) return;
            const mm = String(Math.floor(elapsedSeconds / 60)).padStart(2, '0');
            const ss = String(elapsedSeconds % 60).padStart(2, '0');
            clockEl.textContent = `${mm}:${ss}`;
        }
        renderClock();
        if (clockEl) {
            setInterval(() => {
                if (!isPaused && currentStatus === 'live') {
                    elapsedSeconds += 1;
                    renderClock();
                }
            }, 1000);
        }

        function renderPenaltyDots(kicks) {
            const homeDots = document.getElementById('penalty-dots-home');
            const awayDots = document.getElementById('penalty-dots-away');
            const scoreHome = document.getElementById('penalty-score-home');
            const scoreAway = document.getElementById('penalty-score-away');
            if (!homeDots || !awayDots) return;

            const home = kicks.filter((k) => k.sisi === 'home');
            const away = kicks.filter((k) => k.sisi === 'away');
            const dot = (k) => `<span class="w-3.5 h-3.5 rounded-full ${k.hasil === 'gol' ? 'bg-emerald-500' : 'bg-rose-500'}"></span>`;

            homeDots.innerHTML = home.map(dot).join('');
            awayDots.innerHTML = away.map(dot).join('');
            if (scoreHome) scoreHome.textContent = home.filter((k) => k.hasil === 'gol').length;
            if (scoreAway) scoreAway.textContent = away.filter((k) => k.hasil === 'gol').length;

            if (kicks.length > lastKickCount) {
                const newest = kicks[kicks.length - 1];
                if (newest && newest.hasil === 'gol') {
                    const flash = document.getElementById('penalty-flash');
                    if (flash) {
                        flash.classList.remove('hidden');
                        setTimeout(() => flash.classList.add('hidden'), 2000);
                    }
                }
            }
            lastKickCount = kicks.length;
        }

        function renderEvents(events) {
            const eventsList = document.getElementById('events-list');
            if (!eventsList) return;
            if (!events || events.length === 0) {
                eventsList.innerHTML = '<p id="events-empty" class="text-gray-400 text-xs">Belum ada kejadian.</p>';
                return;
            }
            eventsList.innerHTML = events.map((e) => `
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">B${e.babak ?? '-'}</span>
                    <span class="font-semibold text-gray-700 w-10">${e.menit ?? '-'}</span>
                    <span class="text-gray-600">${e.tipe_label}</span>
                    <span class="text-gray-400">&mdash; ${e.pemain ?? e.team} (${e.team})</span>
                </div>
            `).join('');
        }

        function poll() {
            fetch(root.dataset.pollUrl)
                .then((res) => res.json())
                .then((data) => {
                    const match = data.match;
                    if (!match) return;

                    // Status changed (scheduled→live, live→finished), the
                    // match just entered/left a penalty shootout, or the
                    // babak advanced (clock resets to 0:00) — the
                    // server-rendered structure differs per state, so reload
                    // rather than trying to patch the DOM into a new shape.
                    if (match.status !== currentStatus || match.dalam_adu_penalti !== currentInPenalty || match.babak !== currentBabak) {
                        window.location.reload();
                        return;
                    }

                    if (match.status === 'live') {
                        const scoreEl = document.getElementById('score');
                        if (scoreEl) scoreEl.textContent = match.skor_home + ' – ' + match.skor_away;

                        if (typeof match.elapsed_seconds === 'number') {
                            elapsedSeconds = match.elapsed_seconds;
                            renderClock();
                        }
                        isPaused = !!match.is_paused;

                        document.querySelectorAll('.stat-home, .stat-away').forEach((el) => {
                            const field = el.dataset.field;
                            el.textContent = (match.stat && match.stat[field] !== undefined && match.stat[field] !== null) ? match.stat[field] : 0;
                        });
                        renderEvents(match.events);

                        if (currentInPenalty) {
                            renderPenaltyDots(match.penalti_kicks || []);
                        }
                    }
                })
                .catch(() => {});
        }

        // Poll regardless of initial status — this is what detects a
        // scheduled match going live without the visitor manually refreshing.
        setInterval(poll, 4000);
    })();
</script>
@endpush

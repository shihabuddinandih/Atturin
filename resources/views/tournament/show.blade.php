@extends('tournament.layout')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $tournament->nama_turnamen }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $tournament->lokasi }} &middot; {{ $tournament->tanggal_mulai->translatedFormat('d M Y') }} &ndash; {{ $tournament->tanggal_selesai->translatedFormat('d M Y') }}</p>
    </div>

    <div id="livescore-root" data-poll-url="{{ route('tournament.livescore.poll', $tournament) }}">
        @if($matches->isEmpty())
            <div class="pro-card p-10 text-center">
                <p class="text-sm text-gray-400">Belum ada pertandingan yang dijadwalkan.</p>
            </div>
        @else
            <div class="pro-card overflow-hidden">
                <div class="divide-y divide-gray-50">
                    @foreach($matches as $match)
                        @php
                            $isLive = $match->status === 'live';
                            $isFinished = $match->status === 'finished';
                        @endphp
                        <a href="{{ route('tournament.match.show', [$tournament, $match]) }}"
                           class="match-row flex items-center justify-between gap-4 p-4 sm:p-5 hover:bg-gray-50/50 transition-colors"
                           data-match-id="{{ $match->id }}" data-status="{{ $match->status }}">
                            <div class="min-w-0">
                                <span class="inline-flex px-2 py-0.5 rounded-md text-[9px] font-bold tracking-wide uppercase bg-gray-100 text-gray-500 mb-1.5">
                                    {{ $match->round->nama_ronde }}
                                </span>
                                <p class="text-sm font-semibold text-gray-800 truncate">
                                    {{ $match->teamHome->nama_tim ?? 'TBD' }}
                                    <span class="text-gray-300 font-normal">vs</span>
                                    {{ $match->teamAway->nama_tim ?? 'TBD' }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $match->jadwal_tanggal->translatedFormat('d M Y') }}
                                    @if($match->jadwal_waktu) &middot; {{ \Carbon\Carbon::parse($match->jadwal_waktu)->format('H:i') }} WIB @endif
                                    @if($match->lokasi) &middot; {{ $match->lokasi }} @endif
                                </p>
                            </div>

                            <div class="flex-shrink-0 text-right">
                                @if($isLive)
                                    <div class="flex items-center justify-end gap-2 mb-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 live-dot"></span>
                                        <span class="text-[10px] font-bold text-rose-600 uppercase tracking-wider">Live</span>
                                        <span class="match-minute text-[10px] font-semibold text-gray-400" data-field="minute">{{ $match->isPaused() ? 'Dijeda' : $match->elapsedMinutes() . "'" }}</span>
                                    </div>
                                    <p class="match-score text-lg font-bold text-gray-900">{{ $match->skor_home }} &ndash; {{ $match->skor_away }}</p>
                                @elseif($isFinished)
                                    <span class="inline-flex px-2 py-0.5 rounded-md text-[9px] font-bold tracking-wide uppercase bg-emerald-100 text-emerald-700 mb-1">Selesai</span>
                                    <p class="text-lg font-bold text-gray-900">{{ $match->skor_home }} &ndash; {{ $match->skor_away }}</p>
                                    @if($match->menang_via === 'adu_penalti')
                                        <p class="text-[10px] text-gray-400">adu penalti</p>
                                    @endif
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-md text-[9px] font-bold tracking-wide uppercase bg-gray-100 text-gray-500">Terjadwal</span>
                                @endif
                            </div>
                        </a>
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
        const root = document.getElementById('livescore-root');
        if (!root || !root.dataset.pollUrl) return;

        const knownStatuses = {};
        document.querySelectorAll('.match-row').forEach((row) => {
            knownStatuses[row.dataset.matchId] = row.dataset.status;
        });

        function poll() {
            fetch(root.dataset.pollUrl)
                .then((res) => res.json())
                .then((data) => {
                    const matches = data.matches || [];
                    let needsReload = false;

                    matches.forEach((m) => {
                        const known = knownStatuses[m.id];
                        if (known === undefined) {
                            needsReload = true; // a new scheduled match appeared
                            return;
                        }
                        if (known !== m.status) {
                            needsReload = true;
                        }
                    });

                    if (needsReload) {
                        window.location.reload();
                        return;
                    }

                    matches.forEach((m) => {
                        if (m.status !== 'live') return;
                        const row = document.querySelector(`.match-row[data-match-id="${m.id}"]`);
                        if (!row) return;

                        const scoreEl = row.querySelector('.match-score');
                        if (scoreEl) scoreEl.textContent = m.skor_home + ' – ' + m.skor_away;

                        const minuteEl = row.querySelector('.match-minute');
                        if (minuteEl) {
                            if (m.is_paused) {
                                minuteEl.textContent = 'Dijeda';
                            } else if (m.elapsed_seconds !== null) {
                                minuteEl.textContent = Math.floor(m.elapsed_seconds / 60) + 1 + "'";
                            }
                        }
                    });
                })
                .catch(() => {});
        }

        setInterval(poll, 4000);
    })();
</script>
@endpush

@extends('tournament.layout')

@section('content')
<div class="space-y-6">
    <div class="relative overflow-hidden rounded-2xl {{ $tournament->banner_image ? '' : 'bg-gradient-to-br from-brand-900 via-brand-700 to-brand-500' }} text-white p-6 sm:p-8"
         @if($tournament->banner_image) style="background-image: linear-gradient(to bottom right, rgba(10,22,40,0.85), rgba(0,82,255,0.75)), url('{{ asset('storage/' . $tournament->banner_image) }}'); background-size: cover; background-position: center;" @endif>
        <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wide uppercase bg-white/15 text-white">
            {{ \App\Enums\TournamentStatus::from($tournament->status)->label() }}
        </span>
        <h1 class="text-2xl sm:text-3xl font-bold mt-3">{{ $tournament->nama_turnamen }}</h1>
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 mt-3 text-sm text-white/80">
            <span class="inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M8 3v4M16 3v4M5 6h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z"/></svg>
                {{ $tournament->tanggal_mulai->translatedFormat('d M Y') }} &ndash; {{ $tournament->tanggal_selesai->translatedFormat('d M Y') }}
            </span>
            <span class="inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                {{ $tournament->lokasi }}
            </span>
            <span class="inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4"/></svg>
                {{ $tournament->jumlah_tim }} Tim
            </span>
            <span class="inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                {{ \App\Enums\TournamentFormat::from($tournament->format)->label() }}
            </span>
        </div>
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
                                    @if($match->venueName()) &middot; {{ $match->venueName() }} @endif
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

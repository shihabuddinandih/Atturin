@extends('layouts.admin')

@section('content')
<div class="w-full max-w-7xl mx-auto space-y-6">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-2">
            <a href="{{ route('admin.tournaments.show', $tournament) }}" class="hover:text-brand-500 transition-colors">{{ $tournament->nama_turnamen }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-600">Bracket</span>
        </div>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Kelola Bracket</h1>
                <p class="text-sm text-gray-500 mt-1">Seret tim ke slot kosong untuk menempatkannya di bagan.</p>
            </div>
            <a href="{{ route('admin.tournaments.schedule.index', $tournament) }}" class="px-4 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:border-brand-200 hover:text-brand-600 transition-colors">Lihat Jadwal</a>
        </div>
    </div>

    {{-- Unplaced teams tray --}}
    <div class="pro-card p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">Tim Belum Ditempatkan ({{ $unplacedTeams->count() }})</h3>
        @if($unplacedTeams->isEmpty())
            <p class="text-sm text-gray-400">Semua tim sudah ditempatkan di bagan.</p>
        @else
            <div id="unplaced-teams" class="flex flex-wrap gap-2">
                @foreach($unplacedTeams as $team)
                    <div class="team-chip inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-brand-50 border border-brand-100 text-sm font-semibold text-brand-700 cursor-grab active:cursor-grabbing"
                         draggable="true" data-team-id="{{ $team->id }}">
                        @if($team->logo)
                            <img src="{{ asset('storage/' . $team->logo) }}" class="w-5 h-5 rounded-full object-cover" alt="">
                        @endif
                        {{ $team->nama_tim }}
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Bracket / groups --}}
    @foreach($groupedRounds as $groupLabel => $roundsInGroup)
        @php $isKnockout = $roundsInGroup->first()?->tipe === 'knockout'; @endphp
        <div class="pro-card p-5 overflow-x-auto">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">{{ $groupLabel ? "Grup {$groupLabel}" : 'Bagan Pertandingan' }}</h3>

            @if($isKnockout)
                <div class="bracket-tree flex items-start gap-14 pb-2">
                    @foreach($roundsInGroup->sortBy('urutan') as $round)
                        <div class="bracket-round">
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">{{ $round->nama_ronde }}</p>
                            @foreach($round->matches as $match)
                                @php
                                    $isEntryMatch = $match->home_source_match_id === null && $match->away_source_match_id === null;
                                    $homeIsByeSide = $isEntryMatch && (bool) $match->homeSlot?->is_bye;
                                    $awayIsByeSide = $isEntryMatch && (bool) $match->awaySlot?->is_bye;
                                    $isByeMatch = $isEntryMatch && ($homeIsByeSide !== $awayIsByeSide);
                                @endphp
                                <div class="bracket-match" data-bye="{{ $isByeMatch ? 1 : 0 }}">
                                    <div class="rounded-xl border border-gray-200 overflow-hidden bg-white shadow-sm">
                                        @foreach(['home', 'away'] as $side)
                                            @php
                                                $slot = $side === 'home' ? $match->homeSlot : $match->awaySlot;
                                                $team = $side === 'home' ? $match->teamHome : $match->teamAway;
                                                $sourceRound = $side === 'home' ? optional($match->homeSourceMatch)->round : optional($match->awaySourceMatch)->round;
                                                $isByeSide = $side === 'home' ? $homeIsByeSide : $awayIsByeSide;
                                            @endphp
                                            <div class="px-3 py-1.5 text-xs {{ $side === 'home' ? 'border-b border-gray-100' : '' }} {{ $match->pemenang_team_id && $team && (int) $match->pemenang_team_id === (int) $team->id ? 'bg-emerald-50 font-bold text-emerald-800' : 'text-gray-700' }}">
                                                @if($isByeSide)
                                                    <div class="flex items-center justify-between gap-2">
                                                        <span class="text-gray-300 italic truncate">(bye)</span>
                                                        @if($match->status === 'scheduled')
                                                            <form action="{{ route('admin.tournaments.bracket.unmarkBye', [$tournament, $slot]) }}" method="POST">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="text-gray-300 hover:text-rose-500 flex-shrink-0" title="Batalkan bye">&times;</button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                @elseif($team)
                                                    <div class="flex items-center justify-between gap-2">
                                                        <span class="truncate">{{ $team->nama_tim }}</span>
                                                        @if($slot && $match->status === 'scheduled')
                                                            <form action="{{ route('admin.tournaments.bracket.clearSlot', [$tournament, $slot]) }}" method="POST" onsubmit="return confirm('Kosongkan slot ini?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="text-gray-300 hover:text-rose-500 flex-shrink-0" title="Kosongkan slot">&times;</button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                @elseif($slot)
                                                    <div class="flex items-center gap-1.5">
                                                        <div class="slot-dropzone flex-1 rounded-lg border-2 border-dashed border-gray-200 px-2 py-1 text-gray-400 transition-colors truncate" data-slot-id="{{ $slot->id }}">
                                                            {{ $slot->kode }}
                                                        </div>
                                                        <form action="{{ route('admin.tournaments.bracket.markBye', [$tournament, $slot]) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="text-[9px] font-semibold text-gray-400 hover:text-amber-600 flex-shrink-0" title="Tandai sebagai bye">BYE</button>
                                                        </form>
                                                    </div>
                                                @else
                                                    <span class="text-gray-300 italic truncate block">Menunggu {{ $sourceRound?->nama_ronde ?? 'ronde sebelumnya' }}</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex items-start gap-6 min-w-max pb-2">
                    @foreach($roundsInGroup->sortBy('urutan') as $round)
                        <div class="flex flex-col gap-4 min-w-[220px]">
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">{{ $round->nama_ronde }}</p>
                            @foreach($round->matches as $match)
                                <div class="rounded-xl border border-gray-200 overflow-hidden">
                                    @foreach(['home', 'away'] as $side)
                                        @php
                                            $slot = $side === 'home' ? $match->homeSlot : $match->awaySlot;
                                            $team = $side === 'home' ? $match->teamHome : $match->teamAway;
                                        @endphp
                                        <div class="px-3 py-2 text-xs {{ $side === 'home' ? 'border-b border-gray-100' : '' }} {{ $match->pemenang_team_id && $team && (int) $match->pemenang_team_id === (int) $team->id ? 'bg-emerald-50 font-bold text-emerald-800' : 'text-gray-700' }}">
                                            @if($team)
                                                <div class="flex items-center justify-between gap-2">
                                                    <span>{{ $team->nama_tim }}</span>
                                                    @if($slot && $match->status === 'scheduled')
                                                        <form action="{{ route('admin.tournaments.bracket.clearSlot', [$tournament, $slot]) }}" method="POST" onsubmit="return confirm('Kosongkan slot ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-gray-300 hover:text-rose-500" title="Kosongkan slot">&times;</button>
                                                        </form>
                                                    @endif
                                                </div>
                                            @elseif($slot)
                                                <div class="slot-dropzone rounded-lg border-2 border-dashed border-gray-200 px-2 py-1.5 text-gray-400 transition-colors" data-slot-id="{{ $slot->id }}">
                                                    {{ $slot->kode }} &mdash; kosong
                                                </div>
                                            @else
                                                <span class="text-gray-300 italic">TBD</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/tournament-bracket.js') }}"></script>
<script>
    (function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const assignUrlBase = @json(route('admin.tournaments.bracket.assignSlot', [$tournament, '__SLOT__']));

        document.querySelectorAll('.team-chip').forEach((chip) => {
            chip.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('text/plain', chip.dataset.teamId);
            });
        });

        document.querySelectorAll('.slot-dropzone').forEach((zone) => {
            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                zone.classList.add('border-brand-500', 'bg-brand-50/40', 'text-brand-600');
            });
            zone.addEventListener('dragleave', () => {
                zone.classList.remove('border-brand-500', 'bg-brand-50/40', 'text-brand-600');
            });
            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                const teamId = e.dataTransfer.getData('text/plain');
                if (!teamId) return;

                const url = assignUrlBase.replace('__SLOT__', zone.dataset.slotId);
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'team_id=' + encodeURIComponent(teamId),
                }).then(() => {
                    window.location.reload();
                }).catch(() => {
                    alert('Gagal menempatkan tim. Silakan coba lagi.');
                });
            });
        });

        // Bracket data (schedule/results) isn't as time-sensitive as a live
        // match, so a periodic full reload is simpler and safe here.
        setInterval(() => window.location.reload(), 25000);
    })();
</script>
@endpush
